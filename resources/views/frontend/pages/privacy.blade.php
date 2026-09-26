@extends('frontend.layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="page-hero">
    <div class="container">
        <span class="section-eyebrow"><i class="fas fa-shield-halved"></i> Privacy</span>
        <h1>Privacy Policy</h1>
        <p>How we collect, use and protect your information.</p>
    </div>
</div>

<div class="container table-page" style="max-width:820px;padding-block:40px">

    <div class="card card-pad prose">
        {!! $content ? nl2br(e($content)) : '<p class="text-muted">Our privacy policy is being finalised. For any questions, <a href="'.route('pages.contact').'">contact us</a>.</p>' !!}
    </div>
</div>
@endsection