@extends('frontend.layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
<div class="container table-page" style="max-width:820px;padding-block:48px">
    <div class="page-head hero-head" style="text-align:center;flex-direction:column;align-items:center">
        <h1 class="page-title" style="margin:0">Terms & Conditions</h1>
        <p class="page-sub">The friendly ground rules of our community.</p>
    </div>

    <div class="card card-pad prose">
        {!! $content ? nl2br(e($content)) : '<p class="text-muted">Our terms and conditions are being finalised. For any questions, <a href="'.route('pages.contact').'">contact us</a>.</p>' !!}
    </div>
</div>
@endsection