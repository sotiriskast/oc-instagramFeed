<?php namespace Ideaseven\Instagram;

use Backend;
use Ideaseven\Instagram\Models\Instagram;
use System\Classes\PluginBase;

/**
 * instagram Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'instagram',
            'description' => 'No description provided yet...',
            'author' => 'Sotiris Kastanas',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Ideaseven\Instagram\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'ideaseven.instagram.some_permission' => [
                'tab' => 'instagram',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'instagram' => [
                'label' => 'instagram',
                'url' => Backend::url('ideaseven/instagram/mycontroller'),
                'icon' => 'icon-leaf',
                'permissions' => ['ideaseven.instagram.*'],
                'order' => 500,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Instagram',
                'description' => 'Manage Instagram Integration',
                'category' => 'INTEGRATIONS',
                'icon' => 'octo-icon-instagram',
                'class' => Instagram::class,
                'order' => 500,
                'keywords' => 'instagram integration',
                'permissions' => ['ideaseven.instagram.access_settings'],
            ],
        ];
    }

    public function getInstagramFeed()
    {
        // get settings from configuration
//        $settings = Integration::all();
//        $instagramAccessToken = $settings[0]->content['instagram_access_token'];
//        $numberOfImagesToBeDisplayed = $settings[0]->content['instagram_number_of_images_to_show'];

        $instagramAccessToken = Instagram::get('access_token') ?? '';
        $numberOfImagesToBeDisplayed = Instagram::get('num_images') ?? 5;
        if (!is_numeric($numberOfImagesToBeDisplayed) || $numberOfImagesToBeDisplayed < 1) {
            $numberOfImagesToBeDisplayed = 5;
        }
        // set url
        $url = 'https://graph.instagram.com/me/media?fields=caption,media_url,media_type,permalink&access_token=' . $instagramAccessToken;
        // set up curl
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
//                      CURLOPT_HTTPHEADER => array(
//                          "x-klambrianou-host: klambrianou.com",
//                          "x-klambrianou-key: 376546610428508"
//                      ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        // if there is an error
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            // decode response
            $response = json_decode($response);
            if (!isset($response->data)) return;
            $data = $response->data;
            // remove rows that do not contain images
            foreach ($data as $key => $row) {
                if ($row->media_type !== 'IMAGE') {
                    unset($data[$key]);
                }
            }
            // reindex the array
            $data = array_values($data);
            // if there are too many items, pick off only the number needed
            if (count($data) > $numberOfImagesToBeDisplayed) {
                $reducedData = [];
                for ($i = 1; $i <= $numberOfImagesToBeDisplayed; $i++) {
                    array_push($reducedData, $data[$i]);
                }
                return $reducedData;
            }
            // else just return all the items
            return $data;
        }
    }

    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
            // get settings from configuration
//            $settings = Integration::all();
//            $instagramAccessToken = $settings[0]->content['instagram_access_token'];
            $instagramAccessToken = Instagram::get('access_token') ?? '';

            // set url
            $url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $instagramAccessToken;
            // set up curl
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
               CURLOPT_HTTPHEADER => array(),
            ));
            // save the responce
            $response = curl_exec($curl);
            // save the error
            $err = curl_error($curl);
            // close curl
            curl_close($curl);
            // if there is an error
            if ($err) {
                // log the error
                Log::info("cURL Error #:" . $err);
                $vars = [];
                // send an email alert to ideaseven
                Mail::send(['raw' => 'There was an error with the instagram access token refresh process for "Name", please check the error log for more details.'], $vars, function ($message) {
                    $message->from('mail@example.com', 'Sotiris');
                    $message->to('mail@example.com');
                    $message->subject('"Name" instagram access token has not refreshed');
                });
            }
            // run this task once a week
        })->weekly();
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'getInstagramFeed' => [$this, 'getInstagramFeed'],
            ]
        ];
    }
}
