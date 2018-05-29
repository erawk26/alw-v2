<?php

/**
 * Class SocialSharing_Slider_Module
 * interaction with Grid Gallery plugin
 */
class SocialSharing_Slider_Module extends SocialSharing_Core_Module
{
	private $sliderClass;

	public function onInit()
	{
		parent::onInit();
		add_filter('sss_get_projects_list_for_slider', array($this, 'getProjectsList'), 10, 0);
		add_filter('sss_slider_html', array($this, 'injectProjectHtml'), 10, 1);
		add_action('sss_show_at_slider',array($this,'showAtSlider'), 10,1);
		$this->sliderClass = 'SupsysticSlider';
	}

	/**
	 * change social sharing project settings to show it in grid gallery
	 * @param int $projectId social sharing project id
	 */
	public function showAtSlider($projectId){
		/**
		 * @var SocialSharing_Projects_Module $projectsModule
		 * @var SocialSharing_Projects_Model_Projects $projectsModel
		 */
		$projectsModule = $this->getEnvironment()->getModule('projects');
		$projectsModel = $projectsModule->getModelsFactory()->get('projects');
		$project = $projectsModel->get($projectId);

		$project->settings['where_to_show'] = 'slider';
		$projectsModel->save($project->id,$project->settings);
	}

	/**
	 * disable social sharing on galleries that use it
	 * @param $socialSharingProjectId
	 */
	public function disableSocialSharing($socialSharingProjectId){
		do_action('sss_disable_social_sharing',$socialSharingProjectId);
	}

	/**
	 * clean gallery cache that use social sharing project
	 * @param $projectId
	 */
	public function cleanSocialSharingCache($projectId){
		do_action('sss_clean_cache',$projectId);
	}

	/**
	 * check is gallery is intalled
	 * @return bool true if gallery installed and false otherwise
	 */
	public function isInstalled(){
		return class_exists($this->sliderClass);
	}

	/**
	 * Get HTML of social sharing project
	 * @param int $project_id social sharing project id
	 * @return string HTML of social sharing project
	 */
	public function injectProjectHtml($project_id)
	{
		$project = $this->getProjectsModel()->get($project_id);

		if (null === $project) {
			return '';
		}

		return $this->getProjectsModule()->doShortcode(array(
			'id' => $project->id,
//			'place' => 'slider',
//			'extra' => 'left',
		));
	}

	/**
	 * Get list of social share projects
	 * @return array of pairs where key is project id and value is project title
	 */
	public function getProjectsList(){
		$allProjects = $this->getProjectsModel()->all();
		$projectsList = array();
		if(is_array($allProjects)) {
			foreach($allProjects as $project) {
				$projectsList[$project->id] = $project->title;
			}
		}
		return $projectsList;
	}

	/**
	 * get the social share projects module
	 * @return SocialSharing_Projects_Module
	 */
	protected function getProjectsModule()
	{
		$resolver = $this->getEnvironment()->getResolver();

		return $resolver->getModulesList()->get('projects');
	}

	/**
	 * get the social share projects model
	 * @return SocialSharing_Projects_Model_Projects
	 */
	protected function getProjectsModel(){
		return $this->getProjectsModule()->getModelsFactory()->get('projects');
	}
}