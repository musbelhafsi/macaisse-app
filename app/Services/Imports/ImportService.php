<?php

namespace App\Services\Imports;

use App\Models\Import;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    public function process(Import $import): void
    {
        $path = Storage::path($import->file_path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // Read rows depending on extension
        if (in_array($ext, ['csv', 'txt'])) {
            $rows = $this->readCsv($path);
        } elseif ($ext === 'xlsx') {
            $rows = $this->readXlsx($path); // requires phpoffice/phpspreadsheet or maatwebsite/excel
        } else {
            throw new \RuntimeException("Format de fichier non supporté: .$ext");
        }

        $import->update(['total_rows' => count($rows)]);

        $success = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $this->insertRow($import->entity, $row);
                $success++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'line' => $index + 2, // header (1) + current index (0-based)
                    'error' => $e->getMessage(),
                    'row' => $row,
                ];
            }

            // Progress update (kept simple)
            $import->update([
                'processed_rows' => $index + 1,
                'success_count' => $success,
                'error_count' => count($errors),
                'errors' => array_slice($errors, 0, 50),
            ]);
        }
    }

    private function readCsv(string $path): array
    {
        $data = [];
        if (!is_readable($path)) {
            return $data;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return $data;
        }

        // Detect delimiter from header line (comma or semicolon)
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return $data;
        }
        $firstLine = $this->stripBom($firstLine);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        // Rewind to start and parse with detected delimiter
        rewind($handle);
        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            fclose($handle);
            return $data;
        }
        $headers = array_map(fn($h) => trim(mb_strtolower($h)), $headers);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Pad or trim row to headers length
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), null);
            } elseif (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }
            $assoc = [];
            foreach ($headers as $i => $key) {
                $assoc[$key] = isset($row[$i]) ? trim((string)$row[$i]) : null;
            }
            $data[] = $assoc;
        }
        fclose($handle);
        return $data;
    }

    private function readXlsx(string $path): array
    {
        // Minimal XLSX support if PhpSpreadsheet is installed
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            throw new \RuntimeException('Lecture XLSX non disponible. Installez "maatwebsite/excel" ou "phpoffice/phpspreadsheet".');
        }
        $rows = [];
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $header = [];
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = trim((string)$cell->getFormattedValue());
            }
            if ($rowIndex === 1) {
                $header = array_map(fn($h) => trim(mb_strtolower($h)), $cells);
                continue;
            }
            if (!empty(array_filter($cells, fn($v) => $v !== '' && $v !== null))) {
                $assoc = [];
                foreach ($header as $i => $key) {
                    $assoc[$key] = $cells[$i] ?? null;
                }
                $rows[] = $assoc;
            }
        }
        return $rows;
    }

    private function insertRow(string $entity, array $row): void
    {
        switch ($entity) {
            case 'clients':
                $this->insertClient($row);
                break;

            case 'cheques':
                $this->insertCheque($row);
                break;

            case 'recouvrement_bons':
                $this->insertRecouvrementBon($row);
                break;

            case 'contre_bons':
                $this->insertContreBon($row);
                break;

            case 'expenses':
                $this->insertExpense($row);
                break;

            case 'transfers':
                $this->insertTransfer($row);
                break;

            case 'cash_movements':
                $this->insertCashMovement($row);
                break;

            default:
                throw new \InvalidArgumentException("Entity {$entity} non gérée.");
        }
    }

    // === Insert helpers per entity ===

    private function insertClient(array $row): void
    {
        $name = $row['name'] ?? $row['nom'] ?? null;
        $code = $row['code'] ?? null;
        if (!$name && !$code) {
            throw new \InvalidArgumentException('Client: "name" ou "code" requis.');
        }
        // Ignore duplicates: prefer code, fallback to name
        if ($code) {
            $exists = DB::table('clients')->where('code', $code)->exists();
            if ($exists) return;
        } else {
            $exists = DB::table('clients')->where('name', $name)->exists();
            if ($exists) return;
        }
        DB::table('clients')->insert([
            'name' => $name,
            'code' => $code,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertCheque(array $row): void
    {
        $numero = $row['numero'] ?? null;
        $codeBanque = $row['code_banque'] ?? $row['banque'] ?? null;
        $montant = $this->toDecimal($row['montant'] ?? null);
        $echeance = $this->toDate($row['echeance'] ?? null);
        $dateRecouvrement = $this->toDate($row['date_recouvrement'] ?? null);
        $statut = $row['statut'] ?? null; // en_portefeuille | envoye | remis_banque | rejete | regle

        // lookups
        $clientId = $this->findClientId($row);
        $companyId = $this->findCompanyId($row);
        $livreurId = $this->findLivreurId($row);
        $contreBonId = $this->findContreBonId($row);

        if (!$numero || !$codeBanque || !$clientId || !$companyId || !$livreurId || $montant === null) {
            throw new \InvalidArgumentException('Chèque: champs requis manquants (numero, code_banque, client, company, livreur, montant).');
        }

        // Ignore duplicates by (code_banque, numero)
        $exists = DB::table('cheques')->where('code_banque', $codeBanque)->where('numero', $numero)->exists();
        if ($exists) return;

        DB::table('cheques')->insert([
            'code_banque' => $codeBanque,
            'numero' => $numero,
            'client_id' => $clientId,
            'company_id' => $companyId,
            'livreur_id' => $livreurId,
            'montant' => $montant,
            'echeance' => $echeance,
            'date_recouvrement' => $dateRecouvrement,
            'statut' => in_array($statut, ['en_portefeuille','envoye','remis_banque','rejete','regle']) ? $statut : 'en_portefeuille',
            'contre_bon_id' => $contreBonId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertRecouvrementBon(array $row): void
    {
        $numero = $row['numero'] ?? null;
        $date = $this->toDate($row['date_recouvrement'] ?? $row['date'] ?? null);
        $montant = $this->toDecimal($row['montant'] ?? null);
        $type = strtolower($row['type'] ?? 'espece');
        $note = $row['note'] ?? null;

        $clientId = $this->findClientId($row);
        $companyId = $this->findCompanyId($row);
        $livreurId = $this->findLivreurId($row);
        $contreBonId = $this->findContreBonId($row);

        if (!$numero || !$date || $montant === null || !$clientId || !$companyId || !$livreurId) {
            throw new \InvalidArgumentException('Bon de recouvrement: champs requis manquants.');
        }

        // Ignore duplicates by numero
        if (DB::table('recouvrement_bons')->where('numero', $numero)->exists()) return;

        DB::table('recouvrement_bons')->insert([
            'numero' => $numero,
            'company_id' => $companyId,
            'livreur_id' => $livreurId,
            'date_recouvrement' => $date,
            'client_id' => $clientId,
            'montant' => $montant,
            'type' => in_array($type, ['espece','cheque']) ? $type : 'espece',
            'note' => $note,
            'contre_bon_id' => $contreBonId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertContreBon(array $row): void
    {
        $numero = $row['numero'] ?? null;
        $date = $this->toDate($row['date'] ?? null);
        $montant = $this->toDecimal($row['montant'] ?? 0);
        $nombreBons = (int)($row['nombre_bons'] ?? 0);
        $note = $row['note'] ?? null;

        $companyId = $this->findCompanyId($row);
        $livreurId = $this->findLivreurId($row);

        if (!$numero || !$date || !$companyId || !$livreurId) {
            throw new \InvalidArgumentException('Contre-bon: champs requis manquants.');
        }

        if (DB::table('contre_bons')->where('numero', $numero)->exists()) return;

        DB::table('contre_bons')->insert([
            'numero' => $numero,
            'company_id' => $companyId,
            'livreur_id' => $livreurId,
            'date' => $date,
            'montant' => $montant,
            'nombre_bons' => $nombreBons,
            'ecart' => 0,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertExpense(array $row): void
    {
        $cashId = $this->findCashId($row);
        $date = $this->toDate($row['date'] ?? null);
        $numero = $row['numero'] ?? null;
        $libelle = $row['libelle'] ?? $row['label'] ?? null;
        $montant = $this->toDecimal($row['montant'] ?? null);
        $note = $row['note'] ?? null;

        if (!$cashId || !$date || !$numero || !$libelle || $montant === null) {
            throw new \InvalidArgumentException('Dépense: champs requis manquants.');
        }

        // Ignore duplicates by (cash_id, numero)
        $exists = DB::table('expenses')->where('cash_id', $cashId)->where('numero', $numero)->exists();
        if ($exists) return;

        DB::table('expenses')->insert([
            'cash_id' => $cashId,
            'date' => $date,
            'numero' => $numero,
            'libelle' => $libelle,
            'montant' => $montant,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertTransfer(array $row): void
    {
        $numero = $row['numero'] ?? null;
        $fromCashId = $this->findCashId($row, 'from_cash');
        $toCashId = $this->findCashId($row, 'to_cash');
        $montant = $this->toDecimal($row['montant'] ?? null);
        $note = $row['note'] ?? null;

        if (!$numero || !$fromCashId || !$toCashId || $montant === null) {
            throw new \InvalidArgumentException('Transfert: champs requis manquants.');
        }

        if (DB::table('transfers')->where('numero', $numero)->exists()) return;

        DB::table('transfers')->insert([
            'numero' => $numero,
            'from_cash_id' => $fromCashId,
            'to_cash_id' => $toCashId,
            'montant' => $montant,
            'ecart' => 0,
            'statut' => 'emis',
            'emitted_at' => now(),
            'validated_at' => null,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertCashMovement(array $row): void
    {
        $cashId = $this->findCashId($row);
        $type = strtolower($row['type'] ?? '');
        $montant = $this->toDecimal($row['montant'] ?? null);
        $description = $row['description'] ?? null;
        $date = $this->toDateTime($row['date_mvt'] ?? $row['date'] ?? null);
        $sourceType = $row['source_type'] ?? null;
        $sourceId = isset($row['source_id']) && $row['source_id'] !== '' ? (int)$row['source_id'] : null;

        if (!$cashId || !$type || $montant === null || !$date) {
            throw new \InvalidArgumentException('Mouvement: champs requis manquants.');
        }

        if (!in_array($type, ['recette','depense','transfert_debit','transfert_credit','ajustement'])) {
            throw new \InvalidArgumentException('Mouvement: type invalide.');
        }

        // Ignore duplicates by composite
        $exists = DB::table('cash_movements')
            ->where('cash_id', $cashId)
            ->where('type', $type)
            ->where('montant', $montant)
            ->where('date_mvt', $date)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->exists();
        if ($exists) return;

        DB::table('cash_movements')->insert([
            'cash_id' => $cashId,
            'type' => $type,
            'montant' => $montant,
            'description' => $description,
            'date_mvt' => $date,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // === Lookups & utils ===

    private function findClientId(array $row): ?int
    {
        // direct id
        if (!empty($row['client_id'])) {
            return (int)$row['client_id'];
        }
        // lookup by code
        $code = $row['client_code'] ?? $row['code_client'] ?? null;
        if ($code) {
            $id = DB::table('clients')->where('code', $code)->value('id');
            if ($id) return (int)$id;
        }
        // lookup by name
        $name = $row['client_name'] ?? $row['client'] ?? $row['nom_client'] ?? null;
        if ($name) {
            $id = DB::table('clients')->where('name', $name)->value('id');
            if ($id) return (int)$id;
        }
        return null;
    }

    private function findCompanyId(array $row): ?int
    {
        $id = $row['company_id'] ?? null;
        if ($id) return (int)$id;
        $name = $row['company'] ?? $row['societe'] ?? null;
        if ($name) {
            $val = DB::table('companies')->where('name', $name)->value('id');
            if ($val) return (int)$val;
        }
        return null;
    }

    private function findLivreurId(array $row): ?int
    {
        $id = $row['livreur_id'] ?? null;
        if ($id) return (int)$id;
        $email = $row['livreur_email'] ?? null;
        if ($email) {
            $val = DB::table('users')->where('email', $email)->value('id');
            if ($val) return (int)$val;
        }
        $name = $row['livreur'] ?? null;
        if ($name) {
            $val = DB::table('users')->where('name', $name)->value('id');
            if ($val) return (int)$val;
        }
        return null;
    }

    private function findContreBonId(array $row): ?int
    {
        $numero = $row['contre_bon_numero'] ?? $row['contre_bon'] ?? null;
        if ($numero) {
            $id = DB::table('contre_bons')->where('numero', $numero)->value('id');
            if ($id) return (int)$id;
        }
        return null;
    }

    private function findCashId(array $row, string $prefix = ''): ?int
    {
        $keyId = $prefix ? $prefix . '_id' : 'cash_id';
        $keyName = $prefix ? $prefix . '_name' : 'cash_name';
        if (!empty($row[$keyId])) return (int)$row[$keyId];
        if (!empty($row[$keyName])) {
            $val = DB::table('cash_registers')->where('name', $row[$keyName])->value('id');
            if ($val) return (int)$val;
        }
        return null;
    }

    private function toDecimal($value): ?float
    {
        if ($value === null || $value === '') return null;
        // Replace comma decimal and thousand separators
        $v = str_replace(['\u{00A0}', ' '], '', (string)$value); // remove non-breaking spaces
        $v = str_replace(['.', ','], ['','.' ], $v); // naive: remove thousands dot, use comma as decimal
        if (!is_numeric($v)) {
            // fallback: just replace comma by dot
            $v = str_replace(',', '.', (string)$value);
        }
        return is_numeric($v) ? (float)$v : null;
    }

    private function toDate(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim($v);
        // Accept formats: Y-m-d, d/m/Y, d-m-Y
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'];
        foreach ($formats as $f) {
            $dt = \DateTime::createFromFormat($f, $v);
            if ($dt && $dt->format($f) === $v) {
                return $dt->format('Y-m-d');
            }
        }
        // Try strtotime
        $ts = strtotime($v);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function toDateTime(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim($v);
        $formats = ['Y-m-d H:i:s', 'd/m/Y H:i:s', 'd-m-Y H:i:s', 'Y-m-d', 'd/m/Y', 'd-m-Y'];
        foreach ($formats as $f) {
            $dt = \DateTime::createFromFormat($f, $v);
            if ($dt && $dt->format($f) === $v) {
                return $dt->format('Y-m-d H:i:s');
            }
        }
        $ts = strtotime($v);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function stripBom(string $text): string
    {
        if (substr($text, 0, 3) === "\xEF\xBB\xBF") {
            return substr($text, 3);
        }
        return $text;
    }
}
