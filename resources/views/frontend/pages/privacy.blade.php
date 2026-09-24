@extends('frontend.layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="container table-page" style="max-width:820px;padding-block:48px">
    <div class="page-head hero-head" style="text-align:center;flex-direction:column;align-items:center">
        <h1 class="page-title" style="margin:0">Privacy Policy</h1>
        <p class="page-sub">How we collect, use and protect your information.</p>
    </div>

    <div class="card card-pad prose">
        {!! $content ? nl2br(e($content)) : '<p class="text-muted">Our privacy policy is being finalised. For any questions, <a href="'.route('pages.contact').'">contact us</a>.</p>' !!}
    </div>
</div>
@endsection