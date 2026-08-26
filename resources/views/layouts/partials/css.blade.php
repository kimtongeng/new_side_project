<link href="{{ asset('css/tailwind/app.css?v=' . $asset_v) }}" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('css/vendor.css?v=' . $asset_v) }}">

@if(in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')))
	<link rel="stylesheet" href="{{ asset('css/rtl.css?v=' . $asset_v) }}">
@endif

@yield('css')

<!-- app css -->
<link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">

@if(isset($pos_layout) && $pos_layout)
	<style type="text/css">
		.content {
			padding-bottom: 0px !important;
		}
	</style>
@endif
<style type="text/css">
	/*
	* Pattern lock css
	* Pattern direction
	* http://ignitersworld.com/lab/patternLock.html
	*/
	.patt-wrap {
		z-index: 10;
	}

	.patt-circ.hovered {
		background-color: #cde2f2;
		border: none;
	}

	.patt-circ.hovered .patt-dots {
		display: none;
	}

	.patt-circ.dir {
		background-image: url("{{asset('/img/pattern-directionicon-arrow.png')}}");
		background-position: center;
		background-repeat: no-repeat;
	}

	.patt-circ.e {
		-webkit-transform: rotate(0);
		transform: rotate(0);
	}

	.patt-circ.s-e {
		-webkit-transform: rotate(45deg);
		transform: rotate(45deg);
	}

	.patt-circ.s {
		-webkit-transform: rotate(90deg);
		transform: rotate(90deg);
	}

	.patt-circ.s-w {
		-webkit-transform: rotate(135deg);
		transform: rotate(135deg);
	}

	.patt-circ.w {
		-webkit-transform: rotate(180deg);
		transform: rotate(180deg);
	}

	.patt-circ.n-w {
		-webkit-transform: rotate(225deg);
		transform: rotate(225deg);
	}

	.patt-circ.n {
		-webkit-transform: rotate(270deg);
		transform: rotate(270deg);
	}

	.patt-circ.n-e {
		-webkit-transform: rotate(315deg);
		transform: rotate(315deg);
	}

	/* Daterangepicker Month/Year dropdown color fix */
	.daterangepicker {
		color-scheme: light !important;
	}

	.daterangepicker select.monthselect,
	.daterangepicker select.yearselect,
	.daterangepicker select.hourselect,
	.daterangepicker select.minuteselect,
	.daterangepicker select.secondselect,
	.daterangepicker select.ampmselect {
		color-scheme: light !important;
		background-color: #ffffff !important;
		color: #1f2937 !important;
		border: 1px solid #d1d5db !important;
		border-radius: 6px !important;
		font-size: 13px !important;
		font-weight: 500 !important;
		padding: 3px 6px !important;
		height: 30px !important;
		margin: 0 2px !important;
		cursor: pointer !important;
		outline: none !important;
		box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
		-webkit-appearance: menulist !important;
		-moz-appearance: menulist !important;
		appearance: menulist !important;
	}

	.daterangepicker select.monthselect:focus,
	.daterangepicker select.yearselect:focus,
	.daterangepicker select.monthselect:hover,
	.daterangepicker select.yearselect:hover {
		border-color: #3b82f6 !important;
		background-color: #ffffff !important;
		color: #111827 !important;
		outline: none !important;
		box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
	}

	.daterangepicker select.monthselect option,
	.daterangepicker select.yearselect option,
	.daterangepicker select.hourselect option,
	.daterangepicker select.minuteselect option,
	.daterangepicker select.secondselect option,
	.daterangepicker select.ampmselect option {
		color-scheme: light !important;
		background-color: #ffffff !important;
		color: #1f2937 !important;
		font-size: 13px !important;
		padding: 4px 8px !important;
	}
</style>
@if(!empty($__system_settings['additional_css']))
	{!! $__system_settings['additional_css'] !!}
@endif