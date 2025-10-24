<div>
    @if (session('success'))
        <div role="alert" class="alert alert-success mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

        <button
            type="button"
            class="btn btn-sm btn-soft btn-warning"
            wire:click="toggleCashOpenForm"
{{--            onclick="Livewire.dispatch('toggleViewCashOpen')"--}}
        >
            @if($countOpenCashBox > 0)<div class="badge badge-sm badge-dash badge-info">{{ $countOpenCashBox }}</div>@endif
            Abrir Caja
        </button>

    @if ($showFormCashOpen)
        <div class="modal modal-open">

            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Abrir Caja</h3>
                <div class="space-y-6">
                    @foreach($branchList as $branch)
                        <fieldset class="fieldset" wire:key="branch-{{ $branch['id'] }}">
                            <legend class="fieldset-legend">{{ $branch['name'] }}</legend>
                            @if(!$branch['open'])
                            <div class="join">
                                <div>
                                    <label class="input input-sm validator join-item focus-within:outline-none">
                                        <input wire:model.defer="branchList.branch-{{$branch['id']}}.initial_balance" type="number" placeholder="" />
                                    </label>
                                </div>
                                <button type="button" wire:click="openBranch({{$branch['id']}})" class="btn btn-sm btn-warning join-item">Abrir</button>
                            </div>
                            @error("branchList.branch-{$branch['id']}.initial_balance")
                            <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                            @else
                                <p class="text-sm font-bold"><span class="badge badge-info">Abierto</span> {{ $branch['opening_time'] }}</p>
                            @endif
                        </fieldset>
                    @endforeach


                </div>

                <div class="modal-action">
                    <button type="button" wire:click="toggleCashOpenForm" class="btn btn-sm">Cerrar</button>
                </div>
            </div>

        </div>
    @endif
</div>
