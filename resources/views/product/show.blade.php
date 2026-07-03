<div class="row">
  <div class="col-md-10 col-md-offset-1 col-xs-12">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>@lang('business.location')</th>
            @if(session('business.enable_racks'))
              <th>@lang('lang_v1.rack')</th>
            @endif
            @if(session('business.enable_row'))
              <th>@lang('lang_v1.row')</th>
            @endif
            @if(session('business.enable_position'))
              <th>@lang('lang_v1.position')</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @if(!empty($details[0]))
            @foreach( $details as $detail )
              <tr>
                <td>{{ $detail->name}}</td>
                @if(session('business.enable_racks'))
                  <td>{{ $detail->rack }}</td>
                @endif
                @if(session('business.enable_row'))
                  <td>{{ $detail->row }}</td>
                @endif
                @if(session('business.enable_position'))
                  <td>{{ $detail->position }}</td>
                @endif
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="4" class="text-center">
                -
              </td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>