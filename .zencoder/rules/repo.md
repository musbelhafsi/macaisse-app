# Repo Overview: macaisse

- Name: macaisse
- Framework: Laravel (PHP)
- Key domains:
  - Cash registers and movements (cash_registers, cash_movements)
  - Contre bons and recouvrement
  - Transfers between cash registers
  - Cheques and bordereaux PDF

## Notable Paths
- app/Models: Eloquent models (ContreBon, CashMovement, Transfer, CashRegister, Cheque, RecouvrementBon)
- app/Http/Controllers: TransferController, BordereauController, BordereauPdfController, etc.
- database/migrations: schema for cash_registers, cash_movements, transfers, cheques, etc.
- resources/views: Blade templates (transfers/*, contre_bons/*, auth/*)

## Cash/Movements
- cash_registers fields: id, name, is_main, is_bank (bool), currency, balance
- cash_movements fields: id, cash_id, type(recette|depense|transfert_debit|transfert_credit|ajustement), montant, source_type, source_id, description, date_mvt, balance (computed on create)
- Transfers: emission debits source cash; validation credits destination; now supports immediate validation on create.

## Recent Changes (by assistant)
- Added immediate validation option in transfers/create view and controller logic.
- Added migration to introduce `is_bank` on cash_registers and updated model fillable.

## Setup
- PHP/Laravel standard (composer install, .env, migrations)
- Run migrations: php artisan migrate

## Conventions
- Movement sign handled in model boot (recette/transfert_credit positive; depense/transfert_debit/ajustement negative).
