<x-filament-panels::page>
    <div class="table-container border border-gray-200 rounded-lg relative select-none overflow-x-auto">
        <table id="t-matrix" x-ref="table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky-header">
            <tr id="t-matrix">
                <th scope="col" class="">
                </th>
                @foreach ($uniqueCylinders as $i => $cylinder)
                    <th scope="col" class="px-1 py-1 text-center text-xs font-semibold text-white uppercase tracking-wider bg-success
                        @if($i == 8 || $i == 16) border-r-2 border-r-zinc-600 @endif
                    ">
                        {{ number_format($cylinder, 2) }}
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody
                class="bg-white divide-y divide-gray-200"
            >
            @foreach($matrix as $j => $row)
                <tr class="hover:bg-gray-50 @if($j == 4 || $j == 8 || $j == 16) border-b-2 border-b-zinc-600 @endif">
                    @foreach($row as $i => $opticalProperty)
                        @if($opticalProperty)
                            <td class="px-1 py-1 whitespace-nowrap text-xs text-center font-medium text-white
                                @if(strcmp($type, "+") == 0) bg-blue-500 @else bg-red-500 @endif
                                " >
                                {{ number_format($opticalProperty['sphere'], 2) }}
                            </td>
                            @break
                        @endif
                    @endforeach
                    @foreach($row as $i => $opticalProperty)
                        <td
                            title="{{ $opticalProperty['description'] }}"
                            data-cell-id="{{ $opticalProperty['id'] }}"
                            data-cell-amount="{{ $opticalProperty['amount'] }}"
                            @click="toggleCell({{ (int) $opticalProperty['id'] }})"
                            :class="markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }})
                                ? (markedAmountCells.some(item => item.id === {{ (int) $opticalProperty['id'] }} && item.state=== 'success')
                                ? 'bg-green-400' : 'bg-red-400') : (markedCells.includes({{ (int) $opticalProperty['id'] }})
                                ? 'bg-green-200'
                                : 'bg-white')"
                            class="cursor-pointer px-1 py-1 whitespace-nowrap text-xs font-medium text-center border-t border-r
                            border-l
                            @if($j == 4 || $j == 8 || $j == 16) border-b-2 border-b-zinc-600 @else border-b @endif
                            @if($i == 8 || $i == 16) border-r-2 border-r-zinc-600 @endif

                            @if($j == 12 && $i == 4) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 5) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 12 && $i == 12) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 13) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 12 && $i == 20) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 13 && $i == 21) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 4) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 5) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 12) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 13) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif

                            @if($j == 20 && $i == 20) border-r-2 border-r-zinc-600 border-b-2 border-b-zinc-600 @endif
                            @if($j == 21 && $i == 21) border-l-2 border-l-zinc-600 border-t-2 border-t-zinc-600 @endif
                                "
                        >
                        {{ $opticalProperty['amount'] }}
                            {{-- <div
                                x-text="uploadText({{ $opticalProperty['id'] }}, '{{ $opticalProperty['amount'] }}', '{{ $opticalProperty['description'] }}')"
                            >
                            </div> --}}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
