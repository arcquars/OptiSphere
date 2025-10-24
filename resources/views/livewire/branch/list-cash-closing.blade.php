<div>
    <ul class="menu menu-horizontal bg-base-200">
        @foreach($branches as $branch)
            <li><a wire:click="setBranchSelect({{$branch->id}})" class="@if($branchSelect == $branch->id) menu-active @endif">{{ $branch->name }}</a></li>
        @endforeach
    </ul>
</div>
