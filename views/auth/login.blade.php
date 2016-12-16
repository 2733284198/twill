<!DOCTYPE html>
<html dir="ltr" lang="en-US">
    <head>
        @include('cms-toolkit::layouts.head')
    </head>
    <body>
        <div id="a17" class="login">
            <form accept-charset="UTF-8" action="{{ route('admin.login') }}" class="simple_form credentials" method="post" novalidate="novalidate">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <section class="box box-login">
                    <header>
                        <h3><b>{{ config('app.name') }}</b></h3>
                        <ul>
                            <li><a href="{{ route('admin.password.reset.link') }}">Reset your password</a></li>
                        </ul>
                    </header>
                    @include('cms-toolkit::layouts._flash')
                    <div class="input email required credentials_email field_with_hint">
                        <label class="email required control-label" for="email">
                            Email<abbr title="required">*</abbr>
                        </label>
                        <input class="string email required" id="credentials_email" name="email" type="email"/>
                    </div>
                    <div class="input password required credentials_password field_with_hint">
                        <label class="password required control-label" for="password">
                            Password<abbr title="required">*</abbr>
                        </label>
                        <input class="password required" id="credentials_password" name="password" type="password"/>
                    </div>
                    <footer>
                        <input class="btn btn-small" type="submit" value="Sign in"/>
                    </footer>
                </section>
            </form>
        </div>
    </body>
</html>
