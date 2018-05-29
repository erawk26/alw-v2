<?php

/**
 * Class SocialSharing_Slider_Module
 * interaction with Grid Gallery plugin
 */
class SocialSharing_GoogleMaps_Module extends SocialSharing_Core_Module
{
	/**
	 * @var string
	 */
	private $frameClass;

	/**
	 * @var int
	 */
	private $templateId;

	/**
	 * {@inheritdoc}
	 */
	public function onInit()
	{
		parent::onInit();
		$this->frameClass = 'frameGmp';
		$this->templateId = 10;

		add_filter('supsystic_gmap_sm_html', array($this, 'injectProjectHtml'), 10, 2);
	}

	public function injectProjectHtml($html, $map)
	{
		if (!is_array($map) || !array_key_exists('id', $map)) {
			return $html;
		}

		$search = $this->getSearchString($map['id']);

		/** @var SocialSharing_Projects_Model_Projects $projects */
		$projects = $this->getProjectsModule()->getModelsFactory()->get('projects');
		$project = $projects->searchByElementId($search);

		if (null === $project) {
			return $html;
		}

		return $this->getProjectsModule()->doShortcode(array('id' => $project->id));
	}

	/**
	 * @return bool
	 */
	public function isInstalled()
	{
		return class_exists('frameGmp', false);
	}

	/**
	 * Call method from the Google Maps frame class.
	 * @param string $method Method name.
	 * @param array $arguments An array of the arguments.
	 * @throws BadMethodCallException
	 * @return mixed
	 */
	public function call($method, $arguments = null)
	{
		if (!method_exists($this->getInstance(), $method)) {
			throw new BadMethodCallException(
				sprintf(
					$this->getEnvironment()->translate(
						'Call to undefined method %1$s for %2$s.'
					),
					$method,
					$this->frameClass
				)
			);
		}

		return call_user_func_array(
			array($this->getInstance(), $method),
			$arguments
		);
	}

	/**
	 * Returns gmap module from the Google Maps Easy.
	 * @return gmapGmp
	 */
	public function getModule()
	{
		return $this->findModule('gmap');
	}

	/**
	 * Find and returns Google Maps module.
	 * @param string $name Module name
	 * @return modulePps
	 */
	public function findModule($name)
	{
		return $this->call('getModule', array($name));
	}

	/**
	 * Returns gmap model from the Google Maps Easy.
	 * @return gmapModelGmp
	 */
	public function getModel()
	{
		return $this->getModule()->getModel();
	}

	/**
	 * Returns FrameClass.
	 * @return string
	 */
	public function getFrameClass()
	{
		return $this->frameClass;
	}

	/**
	 * Sets FrameClass.
	 * @param string $frameClass
	 */
	public function setFrameClass($frameClass)
	{
		$this->frameClass = $frameClass;
	}


	protected function getInstance()
	{
		if (!$this->isInstalled()) {
			throw new RuntimeException(
				'Failed to get Google Maps instance. Plugin not activated.'
			);
		}

		return call_user_func_array(array($this->frameClass, '_'), array());
	}

	/**
	 * @return SocialSharing_Projects_Module
	 */
	protected function getProjectsModule()
	{
		$resolver = $this->getEnvironment()->getResolver();

		return $resolver->getModulesList()->get('projects');
	}

	protected function getSearchString($id)
	{
		// Need to be a string
		$id = (string)$id;

		// Map ID saved in the database with the other serialized settings.
		// So we need to serialize value.
		$serialized = serialize(array('map_id' => $id));
		// Take part like s:8:"map_id";s:2:"54";
		$search = substr($serialized, 5, strlen($serialized) - 6);

		return '%' . $search . '%';
	}
}