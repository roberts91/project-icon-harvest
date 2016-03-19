<?php

namespace App\Console\Commands;

use Log;
use App\Icon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Yaml\Parser;

class HarvestIcons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'harvesticons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Harvest icons from Font Awesome and Google Material Design and push them to Algolia.';

    /**
     * Class constrcutor
     */
    function __construct()
    {
        parent::__construct();
        
        // Get file urls
        $this->files = $this->get_files();
    }
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log
        Log::info('Starting IconHarvest');
        
        // Google Material Design Icons
        $material_icons = $this->fetch( 'json', array( 'url' => $this->files->google ) );
        $mi_formatted   = $this->formatResult( $material_icons, 'material_icons' );
        
        // Font Awesome
        $font_awesome   = $this->fetch( 'yaml', array( 'url' => $this->files->fa ) );
        $fa_formatted   = $this->formatResult( $font_awesome, 'fa' );
        
        // Dashicons
        $dashicons           = $this->fetch( 'wp' );
        $dashicons_formatted = $this->formatResult( $dashicons, 'wp' );
        
        echo '<pre>';
        print_r($dashicons_formatted);
        die;
        
        // Merge all icons
        $all_icons = array_merge(
            $mi_formatted, 
            $fa_formatted,
            $dashicons
        );
        
        // Loop through all icons
        foreach($all_icons as $i)
        {
            
            // Define new icon
            $icon = new Icon;
            
            // Check if exists
            $icon = $icon->where('icon_id', '=', $i['icon_id'])->first();
            
            // Check if exists
            if ($icon === null)
            {
                // Define new icon
                $icon = new Icon;
                
                // Insert
                $icon->firstOrCreate($i);
            }
            else
            {
                // Get row
                $icon_check = $icon->where('icon_id', '=', $i['icon_id'])->get()->first()->toArray();
                
                // Unset Laravel-specific fields
                unset($icon_check['id']);
                unset($icon_check['created_at']);
                unset($icon_check['updated_at']);
                
                // Calculate diffs of both key and value
                $diff = array_diff_assoc($icon_check, $i);
                
                // Check if we got diff
                if(is_array($diff) AND count($diff) > 0)
                {
                    // Update
                    $icon->fill($i);
                    $icon->save();
                }
            }
        }
        
        // Log
        Log::info('Stopping IconHarvest');
    }
    
    /**
     * Get array of files containing icons
     *
     * @return object
     */
    private function get_files()
    {
        return (object) [
            'google' => 'https://raw.githubusercontent.com/google/material-design-icons/master/iconfont/MaterialIcons-Regular.ijmap',
            'fa'     => 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/src/icons.yml'
        ];
    }
    
    /**
     * Remove blacklisten words from array of words
     *
     * @return array
     */
    private function removeBlacklistedWords($categories, $blacklist)
    {
        // Abort if no categories
        if(!is_array($categories))
        {
            return false;
        }
        
        // Concatenate and explode array into words
        $category_words = explode(' ', mb_strtolower(implode(' ', $categories)));
        
        // Define var
        $passed = [];
        
        // Loop through words
        foreach($category_words as $word)
        {
            // Add to passed if not in blacklist
            if(!in_array($word, $blacklist))
            {
                // Add to passed-array
                $passed[] = $word;
            }
        }
        
        // Return passed words
        return $passed;
    }

    /**
     * Format icon-result based on which provder
     *
     * @return array
     */
    private function formatResult($content, $type)
    {
        
        // Create resultarray
        $formatted = [];
        
        // Determine provider
        switch($type)
        {
            case 'material_icons':
                
                // Get icons
                $icons = $content->icons;
                
                // Loop through icons
                foreach($icons as $key => $value)
                {
                    
                    // Get name
                    $name = $value->name;
                    
                    // Build ID
                    $id = 'gmi-' . $key;
                    
                    // Build array
                    $formatted[] = array(
                        'icon_id' => $id,
                        'code'    => $key,
                        'type'    => 'gmi',
                        'name'    => $name,
                        'tags'    => null
                    );
                }
                
            break;
            case 'fa':
            
                // Get icons
                $icons = $content['icons'];
                
                // Loop through icons
                foreach($icons as $icon)
                {
                    // Get filter
                    $filter = (isset($icon['filter']) ? $icon['filter'] : []);
                    
                    // Get aliases
                    $aliases = (isset($icon['aliases']) ? $icon['aliases'] : []);
                    
                    // Add fa-{code} to aliases
                    $aliases[] = 'fa-' . $icon['id'];
                        
                    // Get categories
                    $categories = (isset($icon['categories']) ? $icon['categories'] : []);
                    
                    // Define blacklisted words
                    $blacklist = array('web','application', 'icon', 'icons');
                    
                    // Filter out blacklisted words
                    $categories = $this->removeBlacklistedWords($categories, $blacklist);
                    
                    // Merge words
                    $tags = array_merge($filter, $categories, $aliases);
                    
                    // Remove duplicates
                    $tags = array_unique($tags);
                    
                    // Build ID
                    $id = 'fa-' . $icon['unicode'];
                    
                    // Build array
                    $formatted[] = array(
                        'icon_id' => $id,
                        'code'    => $icon['id'],
                        'type'    => 'fa',
                        'name'    => $icon['name'],
                        'tags'    => implode(' ', $tags)
                    );
                }
                
            break;
            case 'wp':
            
                // Get icons
                $icons = $content;
                
                // Loop through icons
                foreach($icons as $key => $value)
                {
                    
                    // Build id
                    $id = 'wp-' . $key;
                    
                    // Build tags
                    $tags = array('dashicon', 'dashicons', $key, $value);
                    
                    // Remove any duplicates
                    $tags = array_unique($tags);
                    
                    // Build array
                    $formatted[] = array(
                        'icon_id' => $id,
                        'code'    => $key,
                        'type'    => 'wp',
                        'name'    => ucfirst($value),
                        'tags'    => implode(' ', $tags)
                    );
                }
            
            break;
            default:
                // Invalid provider
                return false;
        }

        // Return result
        return $formatted;
    }

    /**
     * Fetch content of file and decode in either JSON og YAML
     *
     * @return array/object
     */
    private function fetch($type, $args = array())
    {
        
        // Detemine resulttype
        switch($type)
        {
            case 'json':
            
                // Get url
                $url = $args['url'];
            
                // Fetch content
                $content = file_get_contents($url);
        
                // Check if result is empty
                if(empty($content))
                {
                    // TODO: Make trigger to tell me somthing is wrong
                }
            
                // Try decoding
                if(!$result = json_decode($content))
                {
                    // Failed
                    return false;
                }
                
                // Return data
                return $result;
                
            break;
            case 'yaml':
            
                // Get url
                $url = $args['url'];
            
                // Fetch content
                $content = file_get_contents($url);
    
                // Check if result is empty
                if(empty($content))
                {
                    // TODO: Make trigger to tell me somthing is wrong
                }
            
                // Defining new parser
                $yaml = new Parser();
                
                // Try to parse
                if(!$result = $yaml->parse($content))
                {
                    // Failed
                    return false;
                }
                
                // Return data
                return $result;
                
            break;
            case 'wp':
            
                // Array of dashicoms (fetched from Types-plugin)
                $icons = array(
                    'admin-appearance'        => 'appearance',
                    'admin-collapse'          => 'collapse',
                    'admin-comments'          => 'comments',
                    'admin-generic'           => 'generic',
                    'admin-home'              => 'home',
                    'admin-links'             => 'links',
                    'admin-media'             => 'media',
                    'admin-network'           => 'network',
                    'admin-page'              => 'page',
                    'admin-plugins'           => 'plugins',
                    'admin-post'              => 'post',
                    'admin-settings'          => 'settings',
                    'admin-site'              => 'site',
                    'admin-tools'             => 'tools',
                    'admin-users'             => 'users',
                    'album'                   => 'album',
                    'align-center'            => 'align center',
                    'align-left'              => 'align left',
                    'align-none'              => 'align none',
                    'align-right'             => 'align right',
                    'analytics'               => 'analytics',
                    'archive'                 => 'archive',
                    'arrow-down-alt2'         => 'down alt2',
                    'arrow-down-alt'          => 'down alt',
                    'arrow-down'              => 'down',
                    'arrow-left-alt2'         => 'left alt2',
                    'arrow-left-alt'          => 'left alt',
                    'arrow-left'              => 'left',
                    'arrow-right-alt2'        => 'right alt2',
                    'arrow-right-alt'         => 'right alt',
                    'arrow-right'             => 'right',
                    'arrow-up-alt2'           => 'up alt2',
                    'arrow-up-alt'            => 'up alt',
                    'arrow-up'                => 'up',
                    'art'                     => 'art',
                    'awards'                  => 'awards',
                    'backup'                  => 'backup',
                    'book-alt'                => 'book alt',
                    'book'                    => 'book',
                    'building'                => 'building',
                    'businessman'             => 'businessman',
                    'calendar-alt'            => 'calendar alt',
                    'calendar'                => 'calendar',
                    'camera'                  => 'camera',
                    'carrot'                  => 'carrot',
                    'cart'                    => 'cart',
                    'category'                => 'category',
                    'chart-area'              => 'chart area',
                    'chart-bar'               => 'chart bar',
                    'chart-line'              => 'chart line',
                    'chart-pie'               => 'chart pie',
                    'clipboard'               => 'clipboard',
                    'clock'                   => 'clock',
                    'cloud'                   => 'cloud',
                    'controls-back'           => 'back',
                    'controls-forward'        => 'forward',
                    'controls-pause'          => 'pause',
                    'controls-play'           => 'play',
                    'controls-repeat'         => 'repeat',
                    'controls-skipback'       => 'skip back',
                    'controls-skipforward'    => 'skip forward',
                    'controls-volumeoff'      => 'volume off',
                    'controls-volumeon'       => 'volume on',
                    'dashboard'               => 'dashboard',
                    'desktop'                 => 'desktop',
                    'dismiss'                 => 'dismiss',
                    'download'                => 'download',
                    'editor-aligncenter'      => 'align center',
                    'editor-alignleft'        => 'align left',
                    'editor-alignright'       => 'align right',
                    'editor-bold'             => 'bold',
                    'editor-break'            => 'break',
                    'editor-code'             => 'code',
                    'editor-contract'         => 'contract',
                    'editor-customchar'       => 'custom char',
                    'editor-distractionfree'  => 'distraction free',
                    'editor-expand'           => 'expand',
                    'editor-help'             => 'help',
                    'editor-indent'           => 'indent',
                    'editor-insertmore'       => 'insert more',
                    'editor-italic'           => 'italic',
                    'editor-justify'          => 'justify',
                    'editor-kitchensink'      => 'kitchen sink',
                    'editor-ol'               => 'ol',
                    'editor-outdent'          => 'outdent',
                    'editor-paragraph'        => 'paragraph',
                    'editor-paste-text'       => 'paste text',
                    'editor-paste-word'       => 'paste word',
                    'editor-quote'            => 'quote',
                    'editor-removeformatting' => 'remove formatting',
                    'editor-rtl'              => 'rtl',
                    'editor-spellcheck'       => 'spellcheck',
                    'editor-strikethrough'    => 'strike through',
                    'editor-textcolor'        => 'text color',
                    'editor-ul'               => 'ul',
                    'editor-underline'        => 'underline',
                    'editor-unlink'           => 'unlink',
                    'editor-video'            => 'video',
                    'edit'                    => 'edit',
                    'email-alt'               => 'email alt',
                    'email'                   => 'email',
                    'excerpt-view'            => 'excerpt view',
                    'exerpt-view'             => 'exerpt view',
                    'external'                => 'external',
                    'facebook-alt'            => 'facebook alt',
                    'facebook'                => 'facebook',
                    'feedback'                => 'feedback',
                    'flag'                    => 'flag',
                    'format-aside'            => 'aside',
                    'format-audio'            => 'audio',
                    'format-chat'             => 'chat',
                    'format-gallery'          => 'gallery',
                    'format-image'            => 'image',
                    'format-links'            => 'links',
                    'format-quote'            => 'quote',
                    'format-standard'         => 'standard',
                    'format-status'           => 'status',
                    'format-video'            => 'video',
                    'forms'                   => 'forms',
                    'googleplus'              => 'google plus',
                    'grid-view'               => 'grid view',
                    'groups'                  => 'groups',
                    'hammer'                  => 'hammer',
                    'heart'                   => 'heart',
                    'id-alt'                  => 'id alt',
                    'id'                      => 'id',
                    'images-alt2'             => 'images alt2',
                    'images-alt'              => 'images alt',
                    'image-crop'              => 'image crop',
                    'image-flip-horizontal'   => 'image flip horizontal',
                    'image-flip-vertical'     => 'image flip vertical',
                    'image-rotate-left'       => 'image rotate left',
                    'image-rotate-right'      => 'image rotate right',
                    'index-card'              => 'index card',
                    'info'                    => 'info',
                    'leftright'               => 'left right',
                    'lightbulb'               => 'light bulb',
                    'list-view'               => 'list view',
                    'location-alt'            => 'location alt',
                    'location'                => 'location',
                    'lock'                    => 'lock',
                    'marker'                  => 'marker',
                    'media-archive'           => 'media archive',
                    'media-audio'             => 'media audio',
                    'media-code'              => 'media code',
                    'media-default'           => 'media default',
                    'media-document'          => 'media document',
                    'media-interactive'       => 'media interactive',
                    'media-spreadsheet'       => 'media spreadsheet',
                    'media-text'              => 'media text',
                    'media-video'             => 'media video',
                    'megaphone'               => 'megaphone',
                    'menu'                    => 'menu',
                    'microphone'              => 'microphone',
                    'migrate'                 => 'migrate',
                    'minus'                   => 'minus',
                    'money'                   => 'money',
                    'nametag'                 => 'name tag',
                    'networking'              => 'networking',
                    'no-alt'                  => 'no alt',
                    'no'                      => 'no',
                    'palmtree'                => 'palm tree',
                    'performance'             => 'performance',
                    'phone'                   => 'phone',
                    'playlist-audio'          => 'playlist audio',
                    'playlist-video'          => 'playlist video',
                    'plus-alt'                => 'plus alt',
                    'plus'                    => 'plus',
                    'portfolio'               => 'portfolio',
                    'post-status'             => 'post status',
                    'post-trash'              => 'post trash',
                    'pressthis'               => 'press this',
                    'products'                => 'products',
                    'randomize'               => 'randomize',
                    'redo'                    => 'redo',
                    'rss'                     => 'rss',
                    'schedule'                => 'schedule',
                    'screenoptions'           => 'screen options',
                    'search'                  => 'search',
                    'share1'                  => 'share1',
                    'share-alt2'              => 'share alt2',
                    'share-alt'               => 'share alt',
                    'share'                   => 'share',
                    'shield-alt'              => 'shield alt',
                    'shield'                  => 'shield',
                    'slides'                  => 'slides',
                    'smartphone'              => 'smartphone',
                    'smiley'                  => 'smiley',
                    'sort'                    => 'sort',
                    'sos'                     => 'sos',
                    'star-empty'              => 'star empty',
                    'star-filled'             => 'star filled',
                    'star-half'               => 'star half',
                    'store'                   => 'store',
                    'tablet'                  => 'tablet',
                    'tagcloud'                => 'tag cloud',
                    'tag'                     => 'tag',
                    'testimonial'             => 'testimonial',
                    'text'                    => 'text',
                    'tickets-alt'             => 'tickets alt',
                    'tickets'                 => 'tickets',
                    'translation'             => 'translation',
                    'trash'                   => 'trash',
                    'twitter'                 => 'twitter',
                    'undo'                    => 'undo',
                    'universal-access-alt'    => 'universal access alt',
                    'universal-access'        => 'universal access',
                    'update'                  => 'update',
                    'upload'                  => 'upload',
                    'vault'                   => 'vault',
                    'video-alt2'              => 'video alt2',
                    'video-alt3'              => 'video alt3',
                    'video-alt'               => 'video alt',
                    'visibility'              => 'visibility',
                    'welcome-add-page'        => 'add page',
                    'welcome-comments'        => 'comments',
                    'welcome-edit-page'       => 'edit page',
                    'welcome-learn-more'      => 'learn more',
                    'welcome-view-site'       => 'view site',
                    'welcome-widgets-menus'   => 'widgets menus',
                    'welcome-write-blog'      => 'write blog',
                    'wordpress-alt'           => 'wordpress alt',
                    'wordpress'               => 'wordpress',
                    'yes'                     => 'yes',
                );
                
                return $icons;
            
            break;
        }
        return false;
    }

}
