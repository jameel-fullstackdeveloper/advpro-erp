<div>

<style>



/* Spinner styles */
.spinner {
    display: none; /* Hide by default */
    position: fixed;
    top: 50%;
    left: 50%;
    width: 50px;
    height: 50px;
    border: 6px solid #ccc;
    border-top-color: #2a9d8f;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 9999; /* Ensure it's on top of everything */
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}


</style>

<div wire:loading wire:target="switchCompany" class="spinner"></div>
<?php
//var_dump($companies);
;?>
<!-- Custom dropdown using Bootstrap -->
<div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" id="companyDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border:1px solid #cece">
    {{ $selectedCompany->name ?? 'Switch Segments' }}
    </button>
    <ul class="dropdown-menu" aria-labelledby="companyDropdown">
        @foreach($companies as $company)
            <li>
                <a class="dropdown-item d-flex align-items-center" href="#" wire:click.prevent="switchCompany({{ $company->id }})">
                <img src="{{ $company->avatar ? Storage::disk('spaces')->url($company->avatar) : asset('images/logo-sm-1.png') }}"
                alt="{{ $company->name }} Logo" class="me-2" style="width: 30px; height: 30px;">
                    {{ $company->name }}
                </a>
            </li>
        @endforeach
    </ul>
</div>


<script>
     window.addEventListener('companySwitched', event => {
        window.location.reload(); // Refreshes the current page
    });

</script>
