<!DOCTYPE html>
<html>
    <head>
        <title>Icon check</title>

        <!-- Dependencies for icons -->
        <link href="{{ $icon_files->gmi->frontend }}" rel="stylesheet" type="text/css">
        <link href="{{ $icon_files->fa->frontend }}" rel="stylesheet" type="text/css">
        <link href="{{ asset($icon_files->{'7-stroke'}->frontend) }}" rel="stylesheet" type="text/css">
        <link href="{{ asset($icon_files->wp->frontend) }}" rel="stylesheet" type="text/css">
        
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        
        <!-- Styles -->
        <style>
            
            h1 {
                font-family: 'Lato';
                text-decoration: underline;
                margin-bottom: 0;
            }
            
            small {
                font-family: 'Lato';
                font-size: 17px;
            }
            
            h2 {
                clear:both;
                display:block;
                width:100%;
                margin-top:15px;
                margin-bottom: 5px;
                font-family: 'Lato';
            }
            
            .dashicons {
                height: auto;
                width: auto;
            }
            
            .icons {
                width:100%;
                clear:both;
            }
            
            .icons .icon {
                display: block;
                padding: 5px;
                float: left;
                width: 100px;
                height: 165px;
                text-align: center;
            }
            
            .icons .icon p {
                font-size: 10px;
                font-family: 'Lato';
            }
            
            .icons .icon p.name {
                font-family: 'Lato';
                font-size: 14px;
            }
            
            .icons .icon > span,
            .icons .icon > i {
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
        <small>Project Icon Harvest</small>
        
        <h2>Total count: {{ $icon_count }}</h2>
        
        <div class="icons-outer">
        
        <h2>Font Awesome ({{ count($icons->fa) }})</h2>
        <div class="icons">
            @foreach ($icons->fa as $icon)
            <div class="icon" title="{{ $icon->name }}">
                <i class="fa fa-{{ $icon->code }}"></i>
                <p class="name">{{ $icon->name }}</p>
                <p>{{ $icon->tags }}</p>
            </div>
            @endforeach
        </div>
        
        <div class="clearfix"></div>
        
        <h2>Google Material Design ({{ count($icons->gmi) }})</h2>
        <div class="icons">
            @foreach ($icons->gmi as $icon)
            <div class="icon" title="{{ $icon->name }}">
                <i class="material-icons">&#x{{ $icon->code }};</i>
                <p class="name">{{ $icon->name }}</p>
                <p>{{ $icon->tags }}</p>
            </div>
            @endforeach
        </div>
        
        <div class="clearfix"></div>
        
        <h2>7 Stroke ({{ count($icons->{'7-stroke'}) }})</h2>
        <div class="icons">
            @foreach ($icons->{'7-stroke'} as $icon)
            <div class="icon" title="{{ $icon->name }}">
                <i class="pe-7s-{{ $icon->code }}"></i>
                <p class="name">{{ $icon->name }}</p>
                <p>{{ $icon->tags }}</p>
            </div>
            @endforeach
        </div>
        
        <div class="clearfix"></div>
        
        <h2>WP Dashicons ({{ count($icons->dashicons) }})</h2>
        <div class="icons">
            @foreach ($icons->dashicons as $icon)
            <div class="icon" title="{{ $icon->name }}">
                <span class="dashicons dashicons-{{ $icon->code }}"></span>
                <p class="name">{{ $icon->name }}</p>
                <p>{{ $icon->tags }}</p>
            </div>
            @endforeach
        </div>
        
        </div>
        
    </body>
</html>
