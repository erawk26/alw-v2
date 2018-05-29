<?php

/**
 * Class SupsysticSocialSharing
 */
class SupsysticSocialSharing
{
    private $environment;

    public function __construct()
    {
        if (!class_exists('Rsc_Autoloader', false)) {
            require dirname(dirname(__FILE__)) . '/vendor/Rsc/Autoloader.php';
            Rsc_Autoloader::register();
        }

        $pluginPath = dirname(dirname(__FILE__));
        $pluginName = 'sss';
        $pluginTitleName = 'Social Share by Supsystic';
        $pluginSlug = 'supsystic-social-sharing';
        $environment = new Rsc_Environment($pluginName, '1.9.0', $pluginPath);

        /* Configure */
        $environment->configure(
            array(
                'optimizations'    => 1,
                'environment'      => $this->getPluginEnvironment(),
                'default_module'   => 'projects',
                'lang_domain'      => 'social_sharing',
                'lang_path'        => plugin_basename(
                        dirname(__FILE__)
                    ) . '/langs',
                'plugin_title_name' => $pluginTitleName,
                'plugin_slug' => $pluginSlug,
                'plugin_prefix'    => 'SocialSharing',
                'plugin_source'    => $pluginPath . '/src',
                'plugin_menu'      => array(
                    'page_title' => __(
                        $pluginTitleName,
                        $pluginSlug
                    ),
                    'menu_title' => __(
                        $pluginTitleName,
                        $pluginSlug
                    ),
                    'capability' => 'manage_options',
                    'menu_slug'  => $pluginSlug,
                    'icon_url'   => 'dashicons-share',
                    'position'   => '101.8',
                ),
                'shortcode_name'   => 'grid-gallery',
                'db_prefix'        => 'supsystic_ss_',
                'hooks_prefix'     => 'supsystic_ss_',
                'ajax_url'         => admin_url('admin-ajax.php'),
                'admin_url'        => admin_url(),
                'uploads_rw'       => true,
                'jpeg_quality'     => 95,
                'plugin_db_update' => true,
                'revision'         => 322,
                'welcome_page_was_showed' => get_option($pluginName . '_welcome_page_was_showed'),
                'promo_controller' => 'SocialSharing_Promo_Controller',
	            'pro_button_option_prefix'=>'pro_button_option_',
            )
        );

        $this->environment = $environment;
    }

	public function getEnvironment() {
		return $this->environment;
	}

    public function run()
    {
        // if (isset($_GET['sharing_install_db'])) {
        //     $this->createSchema();
        // }

        /*if (isset($_GET['sharing_reinstall_db'])) {
            $this->dropSchema();
            $this->createSchema();
        }*/

        $this->environment->run();
    }

    public function activate($bootstrap)
    {
        //if is multisite mode
        if (function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            
            $orig_id = $wpdb->blogid;
            $blog_id = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            
            foreach ($blog_id as $id) {
                if (switch_to_blog($id)) {
                    $this->createSchema();

                    $this->installUpdate();
                } 
            }

            switch_to_blog($orig_id);
        } else {
            $this->createSchema();

            $this->installUpdate();
        }
    }

	public function installUpdate()
	{
    	$currentOptionRevision = (int)get_option($this->environment->getPluginName().'_updated');
    	$configRevision = (int)$this->environment->getConfig()->get('revision');
		$updatePath = dirname(__FILE__) . '/configs/';

    	if($currentOptionRevision < $configRevision) {
		    for ($i = $currentOptionRevision; $i <= $configRevision; $i++) {
			    $file = $updatePath . 'rev-'.$i.'.sql';

			    if (!file_exists($file)) {
				    continue;
			    }
			    try {
				    $this->updateFromFile($file);
			    } catch (Exception $e) {
				    if (!$this->environment->isPluginPage()) {
					    return;
				    }
				    wp_die(
					    sprintf(
						    'Failed to update plugin database. Reason: %s',
						    $e->getMessage()
					    )
				    );
			    }
		    }
		    update_option($this->environment->getPluginName().'_updated', $configRevision);
	    }
	}

	/**
	 * Loads updates from the file and update the database.
	 * @param string $file Path to updates file.
	 */
	private function updateFromFile($file)
	{
		if (!is_readable($file)) {
			throw new RuntimeException(
				sprintf('File "%s" is not readable.', $file)
			);
		}

		if (false === $content = file_get_contents($file)) {
			throw new RuntimeException(
				sprintf('Failed to read file "%s".', $file)
			);
		}
		$this->update($content);
	}

	/**
	 * Updates the database.
	 * @param string $data
	 */
	private function update($data)
	{
		global $wpdb;
		if (!function_exists('dbDelta')) {
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		}

		$prefix = $wpdb->prefix . $this->environment->getConfig()->get('db_prefix');
		$data = str_replace('%prefix%', $prefix, $data);

		if ('delete' === substr(strtolower($data), 0, 6)) {
			foreach(explode(';',$data) as $q){
				$wpdb->query($q);
			}
			return;
		}
		// CREATE and INSERT only. No ALTER - dbDella modifies tables itself
		dbDelta('SET FOREIGN_KEY_CHECKS=0');
		dbDelta($data);
		dbDelta('SET FOREIGN_KEY_CHECKS=1');
	}

    public function updateDb() {
        global $wpdb;

        $schema = dirname(__FILE__) . '/configs/dbupdate.sql';
        $networks = dirname(__FILE__) . '/configs/update_networks.sql';
        $prefix = $wpdb->prefix . $this->environment
                ->getConfig()
                ->get('db_prefix');
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        }

        $checkTable = 'SHOW TABLES LIKE "'. $prefix .'views";';

        if(!$wpdb->get_results($checkTable)) {
            $sql = str_replace('%prefix%', $prefix, file_get_contents($schema));

            dbDelta('SET FOREIGN_KEY_CHECKS=0');
            dbDelta($sql);
            dbDelta('SET FOREIGN_KEY_CHECKS=1');
        }

        $sql = str_replace('%prefix%', $prefix, file_get_contents($networks));
        
        dbDelta('SET FOREIGN_KEY_CHECKS=0');
        dbDelta($sql);
        dbDelta('SET FOREIGN_KEY_CHECKS=1');
    }


    public function createSchema()
    {
        global $wpdb;

	    if(get_option($this->environment->getPluginName().'_installed'))
	    	return;

        if (is_file($schema = dirname(__FILE__) . '/configs/dbschema.sql')) {
            $prefix = $wpdb->prefix . $this->environment
                    ->getConfig()
                    ->get('db_prefix');
            
            $sql = str_replace('%prefix%', $prefix, file_get_contents($schema));

            if (!function_exists('dbDelta')) {
                require_once(ABSPATH.'wp-admin/includes/upgrade.php');
            }

            $queryList = array_map('trim', explode(';', $sql));
            
            $wpdb->show_errors = false;

            foreach ($queryList as $query) {
				if(empty($query)) continue;
				$wpdb->query($query);
			}

            $wpdb->show_errors = true;

            update_option($this->environment->getPluginName().'_installed', 1);
        }
    }

    public function dropSchema()
    {
        global $wpdb;

        $prefix = $wpdb->prefix . $this->environment
                ->getConfig()
                ->get('db_prefix');

        $tables = $wpdb->get_results('SHOW TABLES LIKE \''.$prefix.'%\'', ARRAY_N);

        if (count($tables) < 1) {
            return;
        }

        $wpdb->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $inded => $table) {
            $wpdb->query('DROP TABLE IF EXISTS '.array_pop($table).' CASCADE;');
        }

        $wpdb->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function deactivate($bootstrap)
    {
//        register_deactivation_hook($bootstrap, array($this, 'dropSchema'));
    }

    protected function getPluginEnvironment()
    {
        $environment = Rsc_Environment::ENV_PRODUCTION;

        if ((defined('WP_DEBUG') && WP_DEBUG) || (defined(
                    'SUPSYSTIC_SS_DEBUG'
                ) && SUPSYSTIC_SS_DEBUG)
        ) {
            $environment = Rsc_Environment::ENV_DEVELOPMENT;
        }

        if ($_SERVER['SERVER_NAME'] === 'localhost' && $_SERVER['SERVER_PORT'] === '8001') {
            $environment = Rsc_Environment::ENV_DEVELOPMENT;
        }

        return $environment;
    }
}
