<?php

abstract class SocialSharing_Projects_Builder_Standard extends SocialSharing_Projects_Builder
{

     /**
     * Returns default button classes.
     * @return array
     */
    protected function getButtonClasses()
    {
        $name = strtolower($this->getName());
        $project = $this->getProject();
        $classes = array(
            'social-sharing-button',
            'sharer-' . $name,
            'sharer-' . $project->get('design', $name . '-1'),
            'counter-' . strtolower(
                $project->get(
                    'shares_style',
                    SocialSharing_Projects_Builder::COUNTER_STANDARD
                )
            )
        );

        // Gradient mode
        if ($project->has('grad')) {
            $classes[] = 'grad';
        }
        
        // Hide counters
        if (!$project->isDisplayTotalShares()) {
            $classes[] = 'without-counter';
        } else {
            if ($project->isDisplayAllTotalShares()) {
                $classes[] = 'have-all-counter';
            }
        }

        return $classes;
    }

	/**
	 * Returns pro button options.
	 * @return array
	 */
	protected function getButtonProOptions() {

	}
    /**
     * Returns button composite.
     * @param SocialSharing_Projects_Builder_Network $network
     * @return SocialSharing_HtmlBuilder_AbstractElement
     */
    public function getButton(SocialSharing_Projects_Builder_Network $network)
    {
        $builder = $this->getBuilder();
        $project = $this->getProject();
        $classes = $this->getButtonClasses();
        $current = $this->getCurrentPost($project->isSharePostLinkInList());

        if ((int)$project->get('show_counter_after', 0) > $network->getShares()) {
            $classes[] = 'without-counter';
        }

        if (false != $network->getTooltip()) {
            $classes[] = 'tooltip-icon';
        }

        if ($network->getIconImageID()) {
            $classes[] = 'button-icon-image';
        }

        $classes[] = $network->getClass();

        $currentID = $current ? $current->ID : get_the_ID();

        if($current) {
            if ($description = $current->post_excerpt) {
                $description = strip_tags($current->post_excerpt);
                $description = str_replace("", "'", $description);
            } else {
                $description = $current->post_title;
            }
        } else {
            $description = get_bloginfo('description');
        }

        $url = (($project->get('where_to_show') != 'sidebar') OR is_single()) ? $network->getUrl($current,$project) : $network->getUrlCurrentPage($project);

        $atributesButton = array(
            $builder->createAttribute(
                'class',
                $classes
            ),
            $builder->createAttribute(
                'target',
                '_blank'
            ),
            $builder->createAttribute(
                'title',
                $network->getTitle()
            ),
            $builder->createAttribute(
                'href',
                $url
            ),
            $builder->createAttribute(
                'data-nid',
                $network->getId()
            ),
            $builder->createAttribute(
                'data-name',
                $network->getProfileName()
            ),
            $builder->createAttribute(
                'data-pid',
                $project->getId()
            ),
            $builder->createAttribute(
                'data-post-id',
                $currentID
            ),
            $builder->createAttribute(
                'data-url',
                admin_url('admin-ajax.php')
            ),
            $builder->createAttribute(
                'data-url',
                admin_url('admin-ajax.php')
            ),
            $builder->createAttribute(
                'data-description',
                $description
            ),
            $builder->createAttribute(
                'rel',
                'nofollow'
            )
        );

        $atributesIcon = array();

        if ($network->getIconImageID()) {
            $atributesButton[] = $builder->createAttribute(
                'style',
                'background-image: url(' . wp_get_attachment_image_url($network->getIconImageID()) . ');' .
                'background-size: 100% 100%; background-position: center;'
            );
        }

	    $projectSettings = $project->getSettings();

	    // add custom button styles - pro version (and customization is enabled)
	    if(isset($projectSettings['button_enable_customization']) && (int)$projectSettings['button_enable_customization'] && $this->getEnvironment()->isPro()) {
		    // add styles from options
		    $dispatcher = $this->getEnvironment()->getDispatcher();

		    $proSettingsButton = $dispatcher->applyFilters('before_button_options_style_add', array($projectSettings, 0));
		    $proSettingsIcon = $dispatcher->applyFilters('before_button_options_style_add', array($projectSettings, 1));

		    $styleAttribute = null;

	    	foreach($atributesButton as $attribute) {
	    		if($attribute->getName() === 'style') {
				    $styleAttribute = $attribute;
				    break;
			    }
		    }
		    if(!$styleAttribute) {
			    $styleAttribute = $builder->createAttribute(
				    'style',
				    $proSettingsButton
			    );
			    $atributesButton[] = $styleAttribute;
	    	} else {
			    $styleAttribute->setValues($styleAttribute->getValues() + $proSettingsButton);
		    }

		    $styleAttributeIcon = $builder->createAttribute(
			    'style',
			    $proSettingsIcon
		    );
	    }

        $atributesIcon[] = $builder->createAttribute(
            'class',
            array('fa', 'fa-fw', $network->getIcon())
        );

	    // add custom icon styles - pro version
	    if(isset($projectSettings['button_enable_customization']) && (int)$projectSettings['button_enable_customization'] && $this->getEnvironment()->isPro()) {
		    $atributesIcon[] = $styleAttributeIcon;
	    }

        $button = $builder->createElement('a', $atributesButton);

        $icon = $builder->createElement('i', $atributesIcon);

        $counter = $builder->createElement(
            'div',
            array(
                $builder->createAttribute(
                    'class',
                    array(
                        'counter-wrap',
                        $project->get(
                            'shares_style',
                            SocialSharing_Projects_Builder::COUNTER_STANDARD
                        )
                    )
                )
            )
        );

        $counter->addElement(
            $builder->createElement(
                'span',
                array(
                    $builder->createAttribute('class', 'counter')
                )
            )->addElement(
                $builder->createTextElement(
                    $network->getShares(
                        $project->isShortNumbers()
                    )
                )
            )
        );

        $button->addElement($icon);
        $button->addElement($counter);

        return $button;
    }
}
