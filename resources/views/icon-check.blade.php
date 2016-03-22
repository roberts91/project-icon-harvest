<!DOCTYPE html>
<html>
    <head>
        <title>Icon check</title>

        <!-- Dependencies for icons -->
        <link rel="stylesheet" href="{{ $icon_files->gmi->frontend }}">
        <link rel="stylesheet" href="{{ $icon_files->fa->frontend }}">
        <link rel="stylesheet" href="{{ asset($icon_files->{'7-stroke'}->frontend) }}">

        <!-- Styles -->
        <style>
            
            h2 {
                clear:both;
                display:block;
                width:100%;
                margin-top:15px;
                margin-bottom: 5px;
            }
            
            .icons {
                width:100%;
                clear:both;
                display:block;
            }
            
            .icons .icon {
                padding: 5px;
                float:left;
                display:block;
                width: 50px;
                height:50px;
                text-align:center;
            }
            
            .icons .icon > * {
                font-size:42px !important;
            }

            .clearfix:after {
              content:"";
              display:block;
              clear:both;
            }

        </style>
    </head>
    <body>
        
        <h1>Icon check</h1>
            
        <h2>Font Awesome ({{ count($icons->fa) }})</h2>
        <div class="icons">
            @foreach ($icons->fa as $icon)
            <div class="icon">
                <i class="fa fa-{{ $icon->code }}"></i>
            </div>
            @endforeach
        </div>
        
        <div class="clearfix"></div>
        
        <h2>Google Material Design ({{ count($icons->gmi) }})</h2>
        <div class="icons">
            @foreach ($icons->gmi as $icon)
            <div class="icon">
                <i class="material-icons">&#x{{ $icon->code }};</i>
            </div>
            @endforeach
        </div>
        
        <h2>7 Stroke ({{ count($icons->{'7-stroke'}) }})</h2>
        <div class="icons">
            @foreach ($icons->{'7-stroke'} as $icon)
            <div class="icon">
                <i class="pe-7s-{{ $icon->code }}"></i>
            </div>
            @endforeach
        </div>
        
    </body>
</html>
