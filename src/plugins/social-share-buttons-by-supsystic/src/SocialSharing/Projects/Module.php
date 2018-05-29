<?php

/**
 * Class SocialSharing_Projects_Module
 *
 * Allows to manage sharing projects in the plugin.
 */
class SocialSharing_Projects_Module extends SocialSharing_Core_BaseModule
{
    protected $isWPInit = false;

    /**
     * Module initialization.
     */
    public function onInit()
    {
        parent::onInit();

        $this->registerMenu();
        $dispatcher = $this->getEnvironment()->getDispatcher();
        $dispatcher->on('after_ui_loaded', array($this, 'onUiLoaded'));
		if(!is_admin() && !defined( 'DOING_AJAX' )) {
			$dispatcher->on('after_modules_loaded', array($this, 'doFrontendStuff'));
		}

        $projects = $this->getController()->getModelsFactory()->get('projects');

        $dispatcher->on('project_get', array($projects, 'filterGetProject'));
        $dispatcher->filter('before_button_options_style_add', array($this, 'filterProOptions'));

        add_shortcode('supsystic-social-sharing', array($this, 'doShortcode'));

        $this->checkOldProVersion();

        add_action('init', array($this, 'wpInitCallback'));
	    add_action( 'wp_enqueue_scripts',array($this, 'localizeData'));
    }

    public function localizeData()
    {
	    wp_localize_script( 'jquery', 'theme_data', array('themeLogo' => wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ))));
    }

    /** Filters pro options and returns options for button or icon (if $params[1] == true)
     * @param mixed
     * @return mixed
     */
    public function filterProOptions($params)
    {
	    list($settings,$isIcon) = $params;

	    $prefix = $this->getEnvironment()->getConfig()->get('pro_button_option_prefix');
	    $proOptions = array();
		$notPx = array('border-style','border-color','background-color','horizontal-margin','vertical-margin','color','opacity');
    	foreach($settings as $key=>$value) {
    		if(strpos ($key,$prefix)===0) {
			    if($value){
				    $clearedSetting = str_replace( '_', '-', str_replace( $prefix, '', $key ) );

				    $isIconSettings = ( strpos( $clearedSetting, 'icon-' ) !== false ) ? true : false;

				    if ( $isIconSettings ) {
					    $clearedSetting = str_replace( 'icon-', '', $clearedSetting );
				    }

				    if ( ! $isIcon AND $isIconSettings ) {
					    continue;
				    }
				    if ( $isIcon AND ! $isIconSettings ) {
					    continue;
				    }

				    $value = in_array( $clearedSetting, $notPx ) ? $value : $value . 'px';

				    if ( 'vertical-margin' == $clearedSetting ) {
					    $proOptions[] = "margin-top: {$value}px; margin-bottom: {$value}px;";
					    continue;
				    }

				    if ( 'horizontal-margin' == $clearedSetting ) {
					    $proOptions[] = "margin-left: {$value}px;margin-right: {$value}px;";
					    continue;
				    }
				    $proOptions[] = "{$clearedSetting}: {$value};";
		        }
		    }
	    }

	    return $proOptions;
    }

    public function wpInitCallback()
    {
        $this->isWPInit = true;
    }

    /**
     * Fires on after module 'UI' loaded.
     * Loads module assets.
     * @param SocialSharing_Ui_Module $ui UI Module
     */
    public function onUiLoaded(SocialSharing_Ui_Module $ui)
    {
        $environment = $this->getEnvironment();
        $config = $environment->getConfig();
        $version = $config->get('plugin_version');
        $hookName = 'admin_enqueue_scripts';
        $prefix = $config->get('hooks_prefix');

        $ui->addAsset($ui->create('style', 'sss-base-admin')
            ->setModuleSource($this, 'css/base.css')
            ->setHookName($hookName)
        );

        $ui->addAsset($ui->create('style', 'sss-base')
            ->setModuleSource($this, 'css/base.css')
            ->setHookName($prefix . 'before_html_build')
        );

        $ui->addAsset(
            $ui->create('style', 'sss-tooltipster-main')
                ->setModuleSource($this, 'css/tooltipster.css')
                ->setHookName($hookName)
        );

        $ui->addAsset(
            $ui->create('style', 'sss-tooltipster-main')
                ->setModuleSource($this, 'css/tooltipster.css')
                ->setHookName($prefix . 'before_html_build')
        );

        $ui->addAsset(
            $ui->create('style', 'sss-brand-icons')
                ->setModuleSource($this, 'css/buttons/brand-icons.css')
                ->setHookName($prefix . 'before_html_build')
        );

        $ui->addAsset(
            $ui->create('style', 'sss-tooltipster-shadow')
                ->setModuleSource($this, 'css/tooltipster-shadow.css')
                ->setHookName($prefix . 'before_html_build')
        );

        $ui->addAsset(
            $ui->create('style', 'sss-tooltipster-shadow')
                ->setModuleSource($this, 'css/tooltipster-shadow.css')
                ->setHookName($hookName)
        );

        $ui->addAsset($ui->create('script', 'jquery'));

        $ui->addAsset(
            $ui->create('script', 'sss-frontend')
                ->setModuleSource($this, 'js/frontend.js')
                ->setHookName($prefix . 'before_html_build')
                ->addDependency('jquery')
        );

        $ui->addAsset(
            $ui->create('script', 'sss-tooltipster-scripts')
                ->setModuleSource($this, 'js/jquery.tooltipster.min.js')
                ->setHookName($prefix . 'before_html_build')
                ->addDependency('jquery')
        );

        $ui->addAsset(
            $ui->create('script', 'sss-bpopup')
                ->setModuleSource($this, 'js/jquery.bpopup.min.js')
                ->setHookName($prefix . 'before_html_build')
                ->addDependency('jquery')
        );

        $ui->addAsset(
            $ui->create('script', 'sss-jquery-mouseWheel')
                ->setExternalSource(
                    'https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.12/jquery.mousewheel.js'
                )
                ->setHookName($hookName)
                ->setVersion('3.1.12')
        );

        $ui->addAsset(
            $ui->create('script', 'sss-scroll-controller')
                ->setModuleSource($this, 'js/scroll.js')
                ->setHookName($hookName)
                ->setVersion($version)
        );

        $ui->addAsset(
            $ui->create('script', 'sss-networks-controller')
                ->setModuleSource($this, 'js/networks.js')
                ->setHookName($hookName)
                ->setVersion($version)
        );

        $ui->addAsset(
            $ui->create('style', 'sss-projects-styles')
                ->setModuleSource($this, 'css/projects.css')
                ->setHookName($hookName)
                ->setVersion($version)
        );

        if ($environment->isModule('projects', 'index')) {
            $ui->addAsset($ui->create('script', 'jquery-ui-dialog'));

            $ui->addAsset(
                $ui->create('script', 'sss-projects-index')
                    ->setHookName($hookName)
                    ->setModuleSource($this, 'js/index.js')
                    ->setVersion($version)
                    ->addDependency('jquery-ui-dialog')
            );
        }

        // Load only on on admin projects/view or /add
        if ($environment->isModule('projects')
            && ($environment->isAction('view') || $environment->isAction('add'))
        ) {
            $ui->addAsset(
                $ui->create('script', 'jquery-ui-dialog')
            );

            $ui->addAsset(
                $ui->create('script', 'jquery-ui-sortable')
            );

            $ui->addAsset(
                $ui->create('script', 'sss-projects-edit')
                    ->setModuleSource($this, 'js/projects.edit.js')
                    ->setHookName($hookName)
                    ->setVersion($version)
                    ->addDependency('jquery-ui-dialog')
                    ->addDependency('jquery-ui-sortable')
            );

            $ui->addAsset(
                $ui->create('script', 'sss-tooltipster-scripts')
                    ->setModuleSource($this, 'js/jquery.tooltipster.min.js')
                    ->setHookName($hookName)
                    ->setVersion($version)
                    ->addDependency('jquery-ui-dialog')
                    ->addDependency('jquery-ui-sortable')
            );

            $ui->addAsset(
                $ui->create('script', 'sss-settings-dialogs')
                    ->setModuleSource($this, 'js/dialogs.js')
                    ->setHookName($hookName)
                    ->setVersion($version)
                    ->addDependency('jquery-ui-dialog')
                    ->addDependency('jquery-ui-sortable')
            );

	        $ui->addAsset(
		        $ui->create('script', 'wp-color-picker')
			        ->setHookName($hookName));
	        $ui->addAsset(
		        $ui->create('style', 'wp-color-picker')
			        ->setHookName($hookName));

        }

    }

    public function doFrontendStuff()
    {
        $projects = $this->getController()
            ->getModelsFactory()
            ->get('projects', $this)
            ->all();

        if (!is_array($projects) || count($projects) === 0) {
            return;
        }

        foreach ($projects as $project) {
            $this->handleProject($project);
        }
    }

    public function handleProject($project)
    {
        $handler = $this->createHandler(
            new SocialSharing_Projects_Project((array)$project)
        );

        $handler->setWPInit($this->isWPInit);

        return $handler->handle();
    }

    public function doShortcode($attributes)
    {
        $isDebug = defined('WP_DEBUG') && WP_DEBUG;
        $showErrors = $isDebug && (function_exists('is_super_admin') && is_super_admin());

        if (!array_key_exists('id', $attributes)) {
            if ($showErrors) {
                return $this->getEnvironment()->translate('ID is not specified.');
            }

            return null;
        }

        $project = $this->getController()
            ->getModelsFactory()
            ->get('projects', $this)
            ->get($attributes['id']);

        if (!$project) {
            if ($showErrors) {
                return $this->getEnvironment()->translate('Project not found');
            }

            return null;
        }
        
        if (array_key_exists('place', $attributes) && array_key_exists('extra', $attributes)) {
            $project->settings['where_to_show'] = $attributes['place'];
            $project->settings['where_to_show_extra'] = $attributes['extra'];
        }

        $network_settings = array_key_exists('network_settings', $attributes) ? $attributes['network_settings'] : array();
        $this->processedNetworksLink($project, $network_settings);

        return $this->handleProject($project);
    }

    public function registerMenu() {

        $lang = $this->getEnvironment()->getLang();
        $menu = $this->getEnvironment()->getMenu();
        $submenuProjects = $menu->createSubmenuItem();
        $submenuProjectsNew = $menu->createSubmenuItem();

        $submenuProjectsNew->setCapability('manage_options')
            ->setMenuSlug('supsystic-social-sharing&module=projects&action=add')
            ->setMenuTitle($lang->translate('Add new'))
            ->setPageTitle($lang->translate('Add new'))
            ->setModuleName('add-new');
		// Avoid conflicts with old vendor version
		if(method_exists($submenuProjectsNew, 'setSortOrder')) {
			$submenuProjectsNew->setSortOrder(20);
		}

        $menu->addSubmenuItem('add-new', $submenuProjectsNew);

        $submenuProjects->setCapability('manage_options')
            ->setMenuSlug('supsystic-social-sharing&module=projects')
            ->setMenuTitle($lang->translate('Projects'))
            ->setPageTitle($lang->translate('Projects'))
            ->setModuleName('projects');
		// Avoid conflicts with old vendor version
		if(method_exists($submenuProjects, 'setSortOrder')) {
			$submenuProjects->setSortOrder(30);
		}

        $menu->addSubmenuItem('projects', $submenuProjects);
    }

    public function noticeOldVersion()
    {
        $twig = $this->getEnvironment()->getTwig();
        $twig->display(
            'notice.twig',
            array(
                'type' => 'error',
                'message' => $this->getEnvironment()->translate(
                    'Please update your PRO version of the ' .
                    'Supsystic Social Sharing Buttons. ' .
                    'Current PRO version is <strong>incompatible</strong> with ' .
                    'the new version of the plugin.'
                )
            )
        );
    }

    /**
     * Creates and returns project handler.
     * @param \SocialSharing_Projects_Project $project
     * @return \SocialSharing_Projects_Handler
     */
    protected function createHandler(SocialSharing_Projects_Project $project)
    {
        return new SocialSharing_Projects_Handler(
            $project,
            $this->getEnvironment()
        );
    }

    private function checkOldProVersion()
    {
        if (class_exists('SocialSharingPro_Projects_Sharer_Flat', false)) {
            add_action('admin_notices', array($this, 'noticeOldVersion'));
        }
    }

    private function processedNetworksLink(&$project, $network_settings){
        foreach ($project->networks as $network){
            if(isset($network_settings[$network->class]) && strlen($network_settings[$network->class]) > 0){
                $network->url = $network_settings[$network->class];
            }
        }
    }
}