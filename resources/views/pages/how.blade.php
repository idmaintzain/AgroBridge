@extends('layouts.app')
@section('content')
<section class="panel">
    <p class="kicker">How it works</p>
    <h1>Four steps, and the money stays held until the last one is real</h1>
    <ol class="steps">
        <li><strong>The farmer lists produce.</strong> Crop, unit, quantity, price per unit, state, local government, and a collection date.</li>
        <li><strong>The buyer reserves a quantity.</strong> The rest stays on the market. If the buyer never pays, the reservation returns.</li>
        <li><strong>The buyer pays into the hold.</strong> AgroBridge keeps the money. The farmer can see that it is held, and is shown no button that pays them for a claim of delivery.</li>
        <li><strong>Handover is proved.</strong> The buyer confirms collection, or reads out a code the farmer types in. A dispute window then runs. If nobody complains, the farmer is paid the amount agreed when the order was placed.</li>
    </ol>
</section>
@endsection
