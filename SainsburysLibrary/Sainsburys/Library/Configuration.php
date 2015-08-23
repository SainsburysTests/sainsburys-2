<?php

class Sainsburys_Library_Configuration
{

    /**
     * @var Sainsburys_Library_Configuration
     */
    private static $_Instance;

    /**
     * @var array
     */
    private $_settings = array();

    /**
     * @var array
     */
    private $_environmentVariables = array('DEVELOPER',
                                           'ENVIRONMENT',
                                           'PROJECTS_ROOT');

    /**
     * @var string
     */
    private $_projectName;

    /**
     * @var array
     */
    private $_loadedProjects = array();

    public function __construct()
    {
        $this->loadProject('SainsburysLibrary');

    }

    /**
     * @return Sainsburys_Library_Configuration
     */
    public static function instance()
    {
        if (self::$_Instance === null)
        {
            $Instance = new self();
            self::$_Instance = $Instance;
        }
        return self::$_Instance;
    }

    /**
     * @param string $path
     * @return mixed the value of the setting on success; otherwise false
     */
    public function getSetting($path)
    {
        $settings = $this->_settings;
        $pathParts = explode('/', $path);

        foreach ($pathParts as $pathPart)
        {
            if (isset($settings[$pathPart]))
            {
                if (is_array($settings[$pathPart]))
                {
                    $settings = $settings[$pathPart];
                }
                else
                {
                    return $settings[$pathPart];
                }
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * @param string $projectName
     */
    public function loadProject($projectName)
    {
        if (array_search($projectName, $this->_loadedProjects) === false)
        {
            $this->_projectName = $projectName;
            $Document = new Sainsburys_Library_Dom_Document();

            //dev
            $configurationFile = '/home/apache/jia/sainsburys/' .
            					$projectName
                               . '/configuration'
                               . '/DEV.xml';

            if (file_exists($configurationFile))
            {
            	$Document->loadFile($configurationFile);
                $SettingsNode = $Document->getNode('/SAINSBURYS/CONFIGURATION');
                foreach ($SettingsNode->getChildNodes() as $ChildNode)
                {
                    /**
                     * @var Sainsburys_Library_Dom_Node
                     */
                    $ChildNode;
                    $nodeName = $ChildNode->getName();
                    $settings[$nodeName] = $this->_loadSettingsFromNode($ChildNode);
                    $settings = Sainsburys_Library_Function::run()->arrayMerge($this->_settings,
                                                                       $settings);
                    $this->_settings = $settings;
                }
                $this->_loadedProjects[] = $projectName;
            }
            else
            {
            	error_log('[Sainsburys_Library_Configuration] File Not Found: ' . $configurationFile);
            }
        }
    }

    /**
     * @param Sainsburys_Library_Dom_Node $ParentNode
     */
    public function addToNode($ParentNode)
    {
        $ConfigurationNode = $ParentNode->addChildNode('CONFIGURATION');
        $LoadedProjectsNode = $ConfigurationNode->addChildNode('LOADED_PROJECTS');
        foreach ($this->_loadedProjects as $loadedProject)
        {
            $LoadedProjectsNode->addChildNode('PROJECT', $loadedProject);
        }
        $this->_addSettingsToNode($ConfigurationNode, $this->_settings);
    }

    /**
     * @param Sainsburys_Library_Dom_Node $ParentNode
     * @param array $settings
     */
    private function _addSettingsToNode($ParentNode, $settings)
    {
        foreach ($settings as $name => $value)
        {
            if (is_array($value))
            {
                $ChildNode = $ParentNode->addChildNode($name);
                $this->_addSettingsToNode($ChildNode, $value);
            }
            else
            {
                $ChildNode = $ParentNode->addChildNode($name, $value);
            }
        }
    }

    /**
     * @param Sainsburys_Library_Dom_Node $Node
     * @return array the settings
     */
    private function _loadSettingsFromNode($Node)
    {
        $settings = array();
        foreach ($Node->getChildNodes() as $ChildNode)
        {
            /**
             * @var Sainsburys_Library_Dom_Node
             */
            $ChildNode;
            $nodeName = $ChildNode->getName();
            if ($ChildNode->hasChildNodes())
            {
                $settings[$nodeName] = $this->_loadSettingsFromNode($ChildNode);
            }
            else
            {
                if ($nodeName == 'INCLUDE_PATH')
                {
                    $settings[$nodeName] = '[REMOVED]';
                }
                else if ($nodeName == 'SUBPROJECT')
                {
                    $settings[$nodeName] = '[REMOVED]';
                }
                else
                {
                    $settings[$nodeName] = $ChildNode->getValue();
                }
            }
            if ($nodeName == 'INCLUDE_PATH')
            {
                $includePath = get_include_path();
                $projectsRootDir = $this->_settings['PROJECTS_ROOT']
                                 . '/' . $this->_projectName;
                $includePath .= ':' . $projectsRootDir . $ChildNode->getValue();
                set_include_path($includePath);
            }
            else if ($nodeName == 'SUBPROJECT')
            {
                $this->loadProject($ChildNode->getValue());
            }
        }
        return $settings;
    }

}
