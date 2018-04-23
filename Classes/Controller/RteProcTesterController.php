<?php
namespace Colorcube\RteProcTester\Controller;




use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for displaying a page tree with it's content elements
 */
class RteProcTesterController extends BaseScriptClass {


    /**
     * @var array
     */
    public $pageinfo;

    /**
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * The name of the module
     *
     * @var string
     */
    protected $moduleName = 'web_rteproctester';

    /**
     * ModuleTemplate Container
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);

        $this->backendUser = $GLOBALS['BE_USER'];

        $this->MCONF = [
            'name' => $this->moduleName,
        ];
    }


    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return void
     */
    function menuConfig()
    {
        $this->MOD_MENU = Array (
            'function' => Array (
                'db2rte' => 'DB > RTE',
                'rte2db' => 'RTE > DB',
                'db2rte2db' => 'DB > RTE > DB',
                'rte2db2rte' => 'RTE > DB > RTE',
            )
        );
        parent::menuConfig();
    }

    /**
     * Main function of the module. Write the content to $this->content
     *
     * @return void
     */
    public function main()
    {
        $this->thisScript = BackendUtility::getModuleUrl($this->MCONF['name']);
        // Clean up settings:
        $this->MOD_SETTINGS = BackendUtility::getModuleData(
            $this->MOD_MENU,
            GeneralUtility::_GP('SET'),
            $this->MCONF['name']
        );


        $relExtPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('rte_proc_tester');

        $this->moduleTemplate->getPageRenderer()->addCssInlineBlock('html-highlight', '
            .rte-content pre {
                margin: 0;
                padding: 1px;
                display: block;
                font-size: 13px;
                
                
                line-height: 1.3;
                word-break: break-all;
                word-wrap: break-word;
                
                background: none;
                border: none;
            }
            .rte-content pre code {
                font-size: 13px;
                padding: 1em;
                line-height: 1.6;
            }
            '
        );

        $this->moduleTemplate->getPageRenderer()->addCssFile($relExtPath . 'Resources/Public/StyleSheets/gruvbox-light.css');


        // Draw the header
        // page title:
        $this->content .= '<h1>RTE Processing Tester</h1>';
        $this->generateMenu();
        $shortcutName = 'RTE Processing Tester';


        // ShortCut
        $shortcutButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton()
            ->setModuleName($this->MCONF['name'])
            ->setDisplayName($shortcutName)
            ->setSetVariables(['function']);
        $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($shortcutButton);

        $this->moduleContent();
    }


    /**
     * Generates the module content
     *
     * @return	void
     */
    function moduleContent()	{

        $file = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rte_proc_tester').'rte_tsconfig.txt';
        if ($tsConfString = GeneralUtility::_GP('tsconfig')) {
            GeneralUtility::writeFile($file, $tsConfString);
        } else {
            $tsConfString = GeneralUtility::getUrl($file);
        }

        //
        // 1. Read transform config (from ext/rte_proc_tester/rte_tsconfig.txt) and convert to TSconfig array
        //

        $parseObj = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class);
        $parseObj->parse($tsConfString);
        $transConfig = $parseObj->setup;



        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', 'pid='.intval($this->id));
        foreach ($rows as $row) {
            $contentRaw = $row['bodytext'];
            $contentRaw = str_replace(["\r\n", "\r"], "\n", $contentRaw);
            $contentSource = str_replace("\n", '###para###', $contentRaw);


            //
            // 2. Transform DB content to HTML
            //

            $this->content.= '<table border="0" cellspacing="2">';

            if ($this->MOD_SETTINGS['function']==='db2rte') {
                $contentProcessed = $this->transformContent('rte', $contentRaw, $transConfig);
                $this->content.='
                    <tr><th width="50%">DB</th><th width="50%">RTE</th></tr>';
                $this->content.='
                    <tr>
                        <td width="50%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentSource).'</code></pre></td>
                        <td width="50%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentProcessed).'</code></pre></td>
                    </tr>';

            } elseif ($this->MOD_SETTINGS['function']==='rte2db') {
                $contentProcessed = $this->transformContent('db', $contentRaw, $transConfig);
                $this->content.='
                    <tr><th width="50%">RTE</th><th width="50%">DE</th></tr>';
                $this->content.='
                      <tr>
                        <td width="50%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentSource).'</code></pre></td>
                        <td width="50%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentProcessed).'</code></pre></td>
                    </tr>';

            } elseif ($this->MOD_SETTINGS['function']==='db2rte2db') {
                $contentProcessedRTE = $this->transformContent('rte', $contentRaw, $transConfig);
                $contentProcessed = $this->transformContent('db', $contentProcessedRTE, $transConfig);
                $this->content.='
                    <tr><th width="33%">DB</th><th width="34%">RTE</th><th width="33%">DB</th></tr>';
                $this->content.='
                    <tr>
                        <td width="33%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentSource).'</code></pre></td>
                        <td width="34%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentProcessedRTE).'</code></pre></td>
                        <td width="33%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentProcessed).'</code></pre></td>
                    </tr>';

            } elseif ($this->MOD_SETTINGS['function']==='rte2db2rte') {
                $contentProcessed = $this->transformContent('db', $contentRaw, $transConfig);
                $contentProcessedRTE = $this->transformContent('rte', $contentProcessed, $transConfig);
                $contentProcessed = $this->transformContent('db', $contentProcessedRTE, $transConfig);
                $this->content.='
                    <tr><th width="33%">RTE</th><th width="34%">DB</th><th width="33%">RTE</th></tr>';
                $this->content.='
                    <tr>
                        <td width="33%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentSource).'</code></pre></td>
                        <td width="34%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentProcessed).'</code></pre></td>
                        <td width="33%" valign="top" class="rte-content"><pre><code class="html">'.$this->HTML_Highlight($contentProcessedRTE).'</code></pre></td>
                    </tr>';

            }

            $this->content.= '</table>';

        }
        if ($contentRaw===$contentProcessed) {
            $this->content.= '<p>Source == Destination</p>';
        } else {
            $this->content.= '<p>Source != Destination</p>';
        }

        $this->content.= '<br />
            <form action="" method="post">
			<input type="submit" name="save" value="Save" /><br><textarea rows="15" name="tsconfig" wrap="off" style="width:99%;height:65%">'.
            LF . htmlspecialchars($tsConfString).
            '</textarea><br>
			<input type="submit" name="save" value="Save" /></form><br /><br />';

        $this->content.= \TYPO3\CMS\Core\Utility\DebugUtility::viewArray($transConfig);


        $relExtPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('rte_proc_tester');
        $this->content.= '
        <script>
            require([\'' .$relExtPath . 'Resources/Public/JavaScript/highlight.pack.js\'], function(hljs) {
              hljs.initHighlighting();
            });
            </script>';
        #$this->content.= "<script>hljs.initHighlightingOnLoad();</script>";
    }




    /**
     * Performs transformation of content to HTML.
     *
     * @param	string		Transformation direction: db or rte
     * @param	string		Content to transform.
     * @param	array		Configuration array (TSconfig). Contains configuration for transformation information etc.
     * @param	string		Relative path for images/links in RTE; this is used when the RTE edits content from static files where the path of such media has to be transformed forth and back!
     * @param	string		The table name
     * @param	string		The field name
     * @param	integer		pid
     * @return	string		Transformed content
     */
    function transformContent($direction, $content, $transConfig, $RTErelPath='uploads/rte/',$table='dummy',$field='dummy',$pid=0)
    {
        // "special" configuration - what is found at position 4 in the types configuration of a field from record, parsed into an array.
        $specConf = array();

        // Initialize transformation:
        $parseHTML = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Html\RteHtmlParser::class);
        $parseHTML->init($table.':'.$field, $pid);
        // $parseHTML->setRelPath($RTErelPath);

        // Perform transformation:
        // Keyword: "rte" means direction from db to rte, which is to HTML
        $content = $parseHTML->RTE_transform($content, $specConf, $direction, $transConfig);

        return $content;
    }



    /**
     * HTML highlighting
     *
     * @param string $source
     * @param boolean $tidyIndent
     * @return string
     */
    function HTML_Highlight($source, $tidyIndent=true)
    {
        $tmp = htmlspecialchars($source);
        $tmp = str_replace('###para###', '&para;<br />', $tmp);
        return str_replace('&nbsp;', ' ', $tmp);

        #------------------

        #return nl2br(htmlspecialchars($source));

        #------------------

        $tmp=htmlentities($source, ENT_NOQUOTES, 'UTF-8');

        if ($tidyIndent AND function_exists( 'tidy_parse_string' )) {
            $config = array('indent' => TRUE,
                'indent-xhtml' => TRUE,
                'indent-spaces' => 2,
                'wrap' => 200);

            $tidy = tidy_parse_string($tmp, $config, 'UTF8');

            $tidy->cleanRepair();
            $tmp = tidy_get_output($tidy);;
        }


        $tmp = str_replace('###para###', '&para;<br />', $tmp);
        $tmp = str_replace('&nbsp;', ' ', $tmp);
        return nl2br(trim($tmp));
    }


    // ------------------------------------------------------

    /**
     * Injects the request object for the current request or subrequest
     * Then checks for module functions that have hooked in, and renders menu etc.
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['SOBE'] = $this;
        $this->init();
        $this->main();

        $this->moduleTemplate->setContent($this->content);
        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     */
    protected function getButtons()
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        // CSH
        $cshButton = $buttonBar->makeHelpButton()
            ->setModuleName('_MOD_'.$this->moduleName)
            ->setFieldName('');
        $buttonBar->addButton($cshButton, ButtonBar::BUTTON_POSITION_LEFT, 0);
        // View page
        $viewButton = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setOnClick(BackendUtility::viewOnClick(
                $this->pageinfo['uid'],
                '',
                BackendUtility::BEgetRootLine($this->pageinfo['uid'])
            ))
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:labels.showPage'))
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-view', Icon::SIZE_SMALL));
        $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
        // Shortcut
        $shortCutButton = $buttonBar->makeShortcutButton()
            ->setModuleName($this->moduleName)
            ->setDisplayName($this->MOD_MENU['function'][$this->MOD_SETTINGS['function']])
            ->setGetVariables([
                'M',
                'id',
                'function'
            ])
            ->setSetVariables(array_keys($this->MOD_MENU));
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /**
     * Generate the ModuleMenu
     */
    protected function generateMenu()
    {
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier($this->moduleName.'JumpMenu');
        foreach ($this->MOD_MENU['function'] as $controller => $title) {
            $item = $menu
                ->makeMenuItem()
                ->setHref(
                    BackendUtility::getModuleUrl(
                        $this->moduleName,
                        [
                            'id' => $this->id,
                            'SET' => [
                                'function' => $controller
                            ]
                        ]
                    )
                )
                ->setTitle($title);
            if ($controller === $this->MOD_SETTINGS['function']) {
                $item->setActive(true);
            }
            $menu->addMenuItem($item);
        }
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }


}
