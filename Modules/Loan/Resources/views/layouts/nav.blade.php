<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand"
                    href="{{action([\Modules\Loan\Http\Controllers\LoanController::class, 'dashboard'])}}">
                    {{__('Loan::lang.Loan_module')}}
                </a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li @if(request()->segment(2) == 'loans') class="active" @endif>
                        <a href="{{action([\Modules\Loan\Http\Controllers\LoanManagementController::class, 'index'])}}">
                            {{__('Loan::lang.loans')}}
                        </a>
                    </li>
                    <li @if(request()->segment(2) == 'payments') class="active" @endif>
                        <a href="{{ action([\Modules\Loan\Http\Controllers\PaymentsController::class, 'index']) }}">
                            {{ __('Loan::lang.payments') }}
                        </a>
                    </li>
                    <li @if(request()->segment(2) == 'reports') class="active" @endif>
                        <a href="{{ route('Loan.reports.index') }}">
                            {{ __('Loan::lang.reports') }}
                        </a>
                    </li>

                    <li @if(request()->segment(2) == 'settings') class="active" @endif>
                        <a href="{{action([\Modules\Loan\Http\Controllers\SettingsController::class, 'index'])}}">
                            {{__('Loan::lang.settings')}}
                        </a>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>