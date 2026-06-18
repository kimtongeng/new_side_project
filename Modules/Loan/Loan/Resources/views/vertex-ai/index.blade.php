@extends('layouts.app')

@section('title', __('Loan::lang.vertex_ai'))

@section('content')
@include('Loan::layouts.nav')

<div class="container my-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h1 class="display-4 text-primary font-weight-bold">🎥 {{ __('Loan::lang.vertex_ai') }}</h1>
            <p class="lead text-muted">{{ __('Loan::lang.generate_videos_with_vertex_ai') }}</p>
            <hr class="w-25 mx-auto border-primary">
        </div>
    </div>

    <!-- Form Section -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-white text-center py-3">
                    <h4 class="mb-0">🖼️ {{ __('Loan::lang.generate_video') }}</h4>
                </div>
                <div class="card-body bg-light">
                    <form action="{{ route('Loan.vertex-ai.generate') }}" method="POST">
                        @csrf

                        <!-- Prompt -->
                        <div class="form-group mb-4">
                            <label for="prompt" class="font-weight-bold">📝 {{ __('Loan::lang.video_prompt') }}</label>
                            <textarea
                                name="prompt"
                                id="prompt"
                                class="form-control @error('prompt') is-invalid @enderror"
                                rows="4"
                                placeholder="{{ __('Loan::lang.enter_video_prompt') }}"
                                required
                            >{{ old('prompt') }}</textarea>
                            @error('prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Aspect Ratio -->
                        <div class="form-group mb-4">
                            <label for="aspectRatio" class="font-weight-bold">📐 {{ __('Loan::lang.aspect_ratio') }}</label>
                            <select
                                name="aspectRatio"
                                id="aspectRatio"
                                class="form-control @error('aspectRatio') is-invalid @enderror"
                            >
                                <option value="16:9" {{ old('aspectRatio', '16:9') == '16:9' ? 'selected' : '' }}>
                                    16:9 (شاشة عريضة)
                                </option>
                            </select>
                            @error('aspectRatio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Duration -->
                        <div class="form-group mb-4">
                            <label for="duration" class="font-weight-bold">⏳ {{ __('Loan::lang.duration_seconds') }}</label>
                            <select
                                name="duration"
                                id="duration"
                                class="form-control @error('duration') is-invalid @enderror"
                            >
                                @foreach ([5, 6, 7, 8] as $d)
                                    <option value="{{ $d }}" {{ old('duration', 5) == $d ? 'selected' : '' }}>
                                        {{ $d }}
                                    </option>
                                @endforeach
                            </select>
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sample Count -->
                        <div class="form-group mb-4">
                            <label for="sampleCount" class="font-weight-bold">🔢 {{ __('Loan::lang.number_of_videos') }}</label>
                            <select
                                name="sampleCount"
                                id="sampleCount"
                                class="form-control @error('sampleCount') is-invalid @enderror"
                            >
                                @foreach ([1, 2, 3, 4] as $c)
                                    <option value="{{ $c }}" {{ old('sampleCount', 1) == $c ? 'selected' : '' }}>
                                        {{ $c }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sampleCount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                🎬 {{ __('Loan::lang.generate') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Generated Videos -->
    @if (session('videos') && count(session('videos')) > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <h2 class="text-lg font-semibold mb-4">🎥 {{ __('Loan::lang.generated_videos') }}</h2>
                <div class="grid grid-cols-1 gap-4">
                    @foreach (session('videos') as $video)
                        <video controls class="w-full rounded-md">
                            <source src="{{ $video }}" type="video/mp4">
                            {{ __('Loan::lang.browser_does_not_support_video') }}
                        </video>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection