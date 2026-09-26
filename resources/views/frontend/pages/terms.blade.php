@extends('frontend.layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
<div class="page-hero">
    <div class="container">
        <span class="section-eyebrow"><i class="fas fa-file-contract"></i> Legal</span>
        <h1>Terms &amp; Conditions</h1>
        <p>The friendly ground rules of our community.</p>
    </div>
</div>

<div class="container table-page" style="max-width:820px;padding-block:40px">

    <div class="card card-pad prose">
        {!! $content ? nl2br(e($content)) : '<p class="text-muted">Our terms and conditions are being finalised. For any questions, <a href="'.route('pages.contact').'">contact us</a>.</p>' !!}
    </div>
</div>
@endsection