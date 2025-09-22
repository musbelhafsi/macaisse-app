@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouveau bordereau d'envoi</h3>
            <form method="post" action="{{ route('bordereaux.store') }}" class="grid md:grid-cols-2 gap-4 items-end" id="bordereau-form">
                @csrf
                <div>
                    <x-input-label value="Date d'envoi" />
                    <x-text-input type="date" name="date_envoi" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Numéro (auto)" />
                    <x-text-input name="_numero_preview" readonly />
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Note" />
                    <input name="note" class="input input-bordered w-full" />
                </div>

                <div class="md:col-span-2">
                    <h4 class="font-semibold mb-2">Ajouter des lignes</h4>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Ajouter un contre‑bon (par numéro)" />
                            <input type="text" id="cb_search" class="input input-bordered w-full" placeholder="Ex: CB-20250910-001" />
                            <button type="button" class="btn mt-2" id="add_cb_btn">Ajouter</button>
                        </div>
                        <div>
                            <x-input-label value="Ajouter un chèque existant (par ID/numéro)" />
                            <input type="text" id="ch_search" class="input input-bordered w-full" placeholder="Ex: CHQ-1234 ou ID" />
                            <button type="button" class="btn mt-2" id="add_ch_btn">Ajouter</button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-input-label value="Ajouter un chèque ad‑hoc" />
                        <div class="grid md:grid-cols-4 gap-2 items-end">
                            <input class="input input-bordered" placeholder="Banque" id="adhoc_banque" />
                            <input class="input input-bordered" placeholder="Numéro" id="adhoc_numero" />
                            <input class="input input-bordered" placeholder="Montant" id="adhoc_montant" type="number" step="0.01" />
                            <button type="button" class="btn" id="add_adhoc_btn">Ajouter chèque ad‑hoc</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Référence</th>
                                    <th class="text-right">Montant</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="lignes_tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button class="btn btn-primary">Enregistrer</button>
                    <a class="btn" href="{{ route('bordereaux.index') }}">Annuler</a>
                </div>

                <!-- champs cachés pour soumission -->
                <div id="hidden_inputs"></div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const dateEnvoi = document.querySelector('input[name="date_envoi"]');
                    const numeroPreview = document.querySelector('input[name="_numero_preview"]');

                    async function refreshNumero() {
                        if (!dateEnvoi.value) return;
                        try {
                            const res = await fetch(`{{ route('bordereaux.suggest-numero') }}?date=${dateEnvoi.value}`);
                            const data = await res.json();
                            numeroPreview.value = data.suggestion || '';
                        } catch (e) {}
                    }
                    dateEnvoi.addEventListener('change', refreshNumero);
                    refreshNumero();

                    const lignes = []; // {type, ref, label, montant, adhoc?}
                    const tbody = document.getElementById('lignes_tbody');
                    const hidden = document.getElementById('hidden_inputs');

                    function render() {
                        tbody.innerHTML = '';
                        hidden.innerHTML = '';
                        lignes.forEach((l, idx) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${l.type}</td>
                                <td>${l.label}</td>
                                <td class="text-right">${l.montant ? Number(l.montant).toFixed(2) : ''}</td>
                                <td><button type="button" class="btn btn-sm" data-rm="${idx}">Supprimer</button></td>
                            `;
                            tbody.appendChild(tr);

                            if (l.type === 'contre_bon') {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'contre_bon_ids[]';
                                input.value = l.ref;
                                hidden.appendChild(input);
                            } else if (l.type === 'cheque' && !l.adhoc) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'cheque_ids[]';
                                input.value = l.ref;
                                hidden.appendChild(input);
                            } else if (l.type === 'cheque' && l.adhoc) {
                                const wrap = document.createElement('div');
                                wrap.innerHTML = `
                                    <input type="hidden" name="adhoc_cheques[][code_banque]" value="${l.banque||''}">
                                    <input type="hidden" name="adhoc_cheques[][numero]" value="${l.numero||''}">
                                    <input type="hidden" name="adhoc_cheques[][montant]" value="${l.montant||''}">
                                `;
                                hidden.appendChild(wrap);
                            }
                        });
                    }

                    document.getElementById('add_cb_btn').addEventListener('click', async () => {
                        const q = document.getElementById('cb_search').value.trim();
                        if (!q) return;
                        try {
                            const res = await fetch(`{{ route('api.search.contre-bons') }}?q=${encodeURIComponent(q)}`);
                            const data = await res.json();
                            if (data && data.id) {
                                lignes.push({type:'contre_bon', ref:data.id, label:data.numero});
                                render();
                            }
                        } catch (e) {}
                    });

                    document.getElementById('add_ch_btn').addEventListener('click', async () => {
                        const q = document.getElementById('ch_search').value.trim();
                        if (!q) return;
                        try {
                            const res = await fetch(`{{ route('api.search.cheques') }}?q=${encodeURIComponent(q)}`);
                            const data = await res.json();
                            if (data && data.id) {
                                lignes.push({type:'cheque', ref:data.id, label:`${data.numero} — ${data.code_banque}`, montant:data.montant});
                                render();
                            }
                        } catch (e) {}
                    });

                    document.getElementById('add_adhoc_btn').addEventListener('click', () => {
                        const banque = document.getElementById('adhoc_banque').value.trim();
                        const numero = document.getElementById('adhoc_numero').value.trim();
                        const montant = document.getElementById('adhoc_montant').value.trim();
                        if (!numero || !montant) return;
                        lignes.push({type:'cheque', adhoc:true, banque, numero, montant, label:`${numero} — ${banque||''}`, ref:'adhoc'});
                        render();
                    });

                    tbody.addEventListener('click', (e) => {
                        const btn = e.target.closest('button[data-rm]');
                        if (!btn) return;
                        const idx = parseInt(btn.getAttribute('data-rm'));
                        lignes.splice(idx, 1);
                        render();
                    });
                });
            </script>
        </div>
    </div>
</div>
@endsection