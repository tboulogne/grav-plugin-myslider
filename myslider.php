<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Gregwar\Image\Image;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class MysliderPlugin
 * @package Grav\Plugin
 */
class MysliderPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onGetPageTemplates' => ['onGetPageTemplates', 0],
            'onAdminSave' => ['onAdminSave', 0],

        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin

        if (!$this->isAdmin()) {

        // Enable the main event we are interested in
        $this->enable([
            'onPageContentRaw' => ['onPageContentRaw', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onPageInitialized' => ['onPageInitialized', 0],
        ]);
      }else{
        $this->enable([
            'onAdminMenu' => ['onAdminMenu', 0]
        ]);

      }
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onPageContentRaw(Event $e)
    {
        // Get a variable from the plugin configuration
        $text = $this->grav['config']->get('plugins.myslider.text_var');

        // Get the current raw content
        $content = $e['page']->getRawContent();

        // Prepend the output with the custom text and set back on the page
        $e['page']->setRawContent($text . "\n\n" . $content);
    }

    public function onAdminSave(Event $event)
    {
        $page = $event['object'];
        $uri = $this->grav['uri'];
        //dump($page['sliders']);
        $data = $page->toArray();

        if (strpos($uri->path(), $this->config->get('plugins.admin.route').'/plugins/myslider') !== false) {
            //CartUtils::cacheImage($image)

            foreach ($data["sliders"] as $key => $value) {
              if(isset($value["slider_image"])){
                $slide = array_keys($value["slider_image"])[0];
                if(!isset($value[$slide]["slider_cache"])){
                  $data["sliders"][$key]['slider_cache'] = self::cacheImage($slide);
                }
              }
            }
          $page['sliders'] = $data["sliders"] ;
        }

    }

    private static function cacheImage($image){

      $locator = Grav::instance()['locator'];
      $cacheDir = $locator->findResources('cache://images', true) ?: $locator->findResource('cache://images', true, true);
      $cacheDir = is_array($cacheDir) ? $cacheDir[0] : $cacheDir;
      $image = Image::open($image)
      ->setCacheDir($cacheDir)
      ->setActualCacheDir($cacheDir)
      ->cacheFile();
      $remove = getcwd();
      $cache_image = str_replace($remove,'',$image);
      return $cache_image;
    }

    public function onTwigTemplatePaths()
    {
        $twig = $this->grav['twig'];
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }


    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];

        $this->grav['assets']->addCss('plugin://myslider/css/myslider.css');
        $this->grav['assets']->addJs('plugin://myslider/js/front.js', ['group'=>'bottom']);
        //$this->grav['assets']->addJs('plugin://mycart/admin/assets/bootstrap-filestyle.min.js');

    }

    public function onGetPageTemplates(Event $event)
    {
        /** @var Types $types */
        $types = $event->types;
        $types->scanTemplates('plugins://myslider/templates/');
    }



    public function onPageInitialized(Event $event)
    {
      $this->enable([
          'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
      ]);
    }



    public function onAdminMenu()
    {
        $this->grav['twig']->plugins_hooked_nav['PLUGIN_ADMIN.MENU_SLIDER'] = ['route' => 'plugins/myslider', 'icon' => 'fa-gear'];
    }
}
