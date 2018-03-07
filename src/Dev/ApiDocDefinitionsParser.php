<?php


namespace alexshadie\TelegramBot\Dev;


class ApiDocDefinitionsParser
{
    const TELEGRAM_API_DOC_URL = "https://core.telegram.org/bots/api";
    /** @var \phpQuery */
    private $document;

    /** @var string[] */
    private $types = [];
    /** @var ApiDocBlock[] */
    private $blocks = [];
    /** @var string[] */
    private $files = [];

    public function __construct()
    {
        $webpageContent = file_get_contents(self::TELEGRAM_API_DOC_URL);
        $this->document = \phpQuery::newDocument($webpageContent);
    }

    public function parse()
    {
        $content = $this->document->find('#dev_page_content');
//        $content->find();
        $html = str_replace("<h4>", "##END####BEGIN##<h4>", $content->html());
        $matches = [];
        preg_match_all("!##BEGIN##(.+)##END!Uism", $html . "##END##", $matches);

        $this->blocks = [];

        foreach ($matches[1] as $text) {
            $this->blocks[] = new ApiDocBlock($text);
        }


        /** @var ApiDocBlock $block */
        foreach ($this->blocks as $block) {
            if ($block->getType() === 'object') {
                $this->types[] = $block->getName();
            }
        }


        foreach ($this->blocks as $block) {
            if ($block->getType() === 'method') {
                $block->recognizeReturnType($this->types);
            }
        }
    }

    private function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
//                $results[] = $path;
            }
        }

        return $results;
    }

    public function loadFiles()
    {
        $root = realpath(__DIR__ . "/../");
        foreach ($this->getDirContents($root) as $filename) {
            $this->files[str_replace(".php", "", basename($filename))] = str_replace(realpath(__DIR__ . "/../") . "/", "", $filename);
        }
    }

    public function buildObjectFiles()
    {
        foreach ($this->blocks as $block) {
            if ($block->getType() == 'object') {
                echo $block->getName() . " - " . ($this->files[$block->getName()] ?? "no file") . "\n\n";
                $fileContents = $this->getFile($block, ($this->files[$block->getName()] ?? null));
                echo $fileContents . "\n\n";
                file_put_contents(
                    $this->files[$block->getName()] ?? realpath(__DIR__ . "/../Misc/") . "{$block->getName()}.php",
                    $fileContents
                );
            }
        }
    }

    public function splitLineByChunks($line, $maxlen = 120)
    {
        $result = [];
        while (strlen($line) > $maxlen) {
            for ($i = $maxlen; $line[$i] != " " && $i > 0; $i--);

            if ($i > 0) {
                $result[] = substr($line, 0, $i);
                $line = substr($line, $i + 1);
            }
        }
        $result[] = $line;
        return $result;
    }

    private function getNamespace($typename)
    {
        return "alexshadie\\TelegramBot\\" . $this->getNsType(str_replace(["/", ".php"], ["\\", ""], $this->files[$typename] ?? "Misc/{$typename}.php"));
    }

    private function getCoreType($typename)
    {
        if ($typename == "Integer" || $typename == "int") return "int";
        if ($typename == "String" || $typename == "string") return "string";
        return null;
    }

    private function getDocType($typename)
    {
        if ($this->getCoreType($typename)) {
            return $this->getCoreType($typename);
        }
        if (strpos($typename, "Array of ") !== false) {
            return str_replace("Array of ", "", $typename) . "[]";
        }
        return $typename;
    }

    private function getNsType($typename)
    {
        if ($this->getCoreType($typename)) {
            return $this->getCoreType($typename);
        }
        if (strpos($typename, "Array of ") !== false) {
            return str_replace("Array of ", "", $typename);
        }
        return $typename;
    }

    private function getRetType($typename)
    {
        if ($this->getCoreType($typename)) {
            return $this->getCoreType($typename);
        }
        if (strpos($typename, "Array of ") !== false) {
            return "array";
        }
        return $typename;
    }

    private function underscoreToCamelCase($string, $lcfirst = false)
    {
        $string = explode("_", $string);
        $string = implode("", array_map('ucfirst', $string));
        if ($lcfirst) {
            $string = lcfirst($string);
        }
        return $string;
    }

    public function getFile(ApiDocBlock $block, $file)
    {
        if (!$file) {
            $file = "Misc/" . $block->getName();
        }

        $header = [];
        $use = [];
        $class = [];
        $props = [];
        $ctor = [];
        $ctorDoc = [];
        $ctorArgs = [];
        $ctorBody = [];
        $getters = [];
        $methods = [];
        $create = [];

        $testClass = [];
        $testMethods = [];


        $header = ["<?php", "", "namespace alexshadie\\TelegramBot\\" . str_replace(["/", ".php"], ["\\", ""], $file) . ";", ""];
        $use["Object"] = "use " . $this->getNamespace("Object") . ";";
        $ctorDoc[] = "    /**";
        $ctorDoc[] = "     * {$block->getName()} constructor.";
        $ctorDoc[] = "     *";


        $class[] = "/**";
        $desc = explode("\n", $block->getDescription());
        foreach ($desc as $lines) {
            foreach ($this->splitLineByChunks($lines, 117) as $line) {
                $class[] = " * " . $line;
            }
            $class[] = " *";
        }
        $class[] = " */";
        $class[] = "class {$block->getName()} extends Object";
        $class[] = "{";

        $testClass[] = "class Test{$block->getName()} extends TestCase";
        $testClass[] = "{";

        $create[] = "    /**";
        $create[] = "      * Creates " . $block->getName() . " object from data.";
        $create[] = "      * @param \stdClass \$data";
        $create[] = "      * @return " . $block->getName();
        $create[] = "      */";
        $create[] = "    public static function createFromObject(?\stdClass \$data): ?" . $block->getName();
        $create[] = "    {";
        $create[] = "        if (is_null(\$data)) {";
        $create[] = "            return null;";
        $create[] = "        }";
        $create[] = "        \$object = new " . $block->getName() . "();";

        if ($block->getArgs()) {
            foreach ($block->getArgs() as $property) {
                $props[] = "    /**";
                $getters[] = "    /**";
                $desc = explode("\n", $property['description']);
                foreach ($desc as $lines) {
                    foreach ($this->splitLineByChunks($lines, 117) as $line) {
                        $props[] = "     * " . $line;
                        $getters[] = "     * " . $line;
                    }
                    $props[] = "     *";
                    $getters[] = "     *";
                }
                $props[] = "     * @var " . $this->getDocType($property['type']) . ($property['optional'] ? "|null" : "");
                $getters[] = "     * @return " . $this->getDocType($property['type']) . ($property['optional'] ? "|null" : "");
                if (!$this->getCoreType($property['type'])) {
                    $use[$property['type']] = "use " . $this->getNamespace($property['type']) . ";";
                }
                $props[] = "     */";
                $getters[] = "     */";
                $props[] = "    private \${$property['name']};";
                $props[] = "";
                $getters[] = "    public function get" . $this->underscoreToCamelCase($property['name']) . "(): " . ($property['optional'] ? "?" : "") . $this->getRetType($property['type']);
                $getters[] = "    {";
                $getters[] = "        return \$this->{$property['name']};";
                $getters[] = "    }";
                $getters[] = "";

                $ctorArgs[] =
                    ($property['optional'] ? "?" : "") . $this->getRetType($property['type']) . " " .
                    "\${$this->underscoreToCamelCase($property['name'], true)}";

                $ctorDoc[] = "     * @param \$" . $this->underscoreToCamelCase($property['name'], true) . " " . $this->getDocType($property['type']) . ($property['optional'] ? "|null" : "");

                $ctorBody[] = "        \$this->" . $property['name'] . " = \$data->" . $property['name'] . ";";

                if ($this->getCoreType($property['type'])) {
                    $create[] = "        \$object->" . $property['name'] . " = \$data->" . $property['name'] . ($property['optional'] ? " ?? null" : "") . ";";
                } else {
                    $create[] = "        \$object->" . $property['name'] . " = new " . $this->getNsType($property['type']) . "(\$data->" . $property['name'] . ($property['optional'] ? " ?? null" : "") . ");";
                }
            }
        }

        $use[] = "";
        $create[] = "        return \$object;";
        $create[] = "    }";
        $create[] = "";
        $create[] = "    /**";
        $create[] = "      * Creates array of " . $block->getName() . " objects from data.";
        $create[] = "      * @param array \$data";
        $create[] = "      * @return " . $block->getName() . "[]";
        $create[] = "      */";
        $create[] = "    public static function createFromObject(?array \$data): ?array";
        $create[] = "    {";
        $create[] = "        if (is_null(\$data)) {";
        $create[] = "            return null;";
        $create[] = "        };";
        $create[] = "        \$objects = [];";
        $create[] = "        foreach (\$data as \$row) {";
        $create[] = "            \$objects[] = static::createFromObject(\$row);";
        $create[] = "        }";
        $create[] = "        return \$objects;";
        $create[] = "    }";
        $create[] = "";

        $ctorDoc[] = "     */";

        $ctor = array_merge(
            $ctorDoc,
            ["    public function __construct(" . implode(", ", $ctorArgs) . ")", "    {"],
            $ctorBody,
            ["    }", ""]
        );

        $content = array_merge(
            $header,
            $use,
            $class,
            $props,
            $ctor,
            $getters,
            $methods,
            $create
        );

        $testContent = array_merge(
            $header,
            $use,
            $testClass
        );

        $content[] = "}";
        $content[] = "";

        $testContent[] = "}";
        $testContent[] = "";

        return join("\n", $content);
    }

}