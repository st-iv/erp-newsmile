<?

namespace Mmit\NewSmile;

use Bitrix\Main\Type\Date;

class Helpers
{
    protected static $usedIds = [];

    /**
     * @var \DateTime $currentDate
     */
    protected static $currentDate;

    /**
     * Получает из массива групп дерево
     * @param array $groups
     * @param string $parentGroupField - название ключа, в котором хранится id родительской группы
     * @param string $childsField - название ключа, по которому будут хранитсья подгруппы
     *
     * @return array
     */
    public static function getTree(array $groups, $parentGroupField = 'GROUP_ID', $childsField = 'SUBGROUPS')
    {
        foreach ($groups as &$group)
        {
            if($group[$parentGroupField])
            {
                $groups[ $group[$parentGroupField] ][$childsField][] =& $group;
            }
        }
        unset($group);

        foreach ($groups as $groupId => $group)
        {
            if($group[$parentGroupField])
            {
                unset($groups[$groupId]);
            }
        }

        return $groups;
    }

    /**
     * Сортировка групп к виду развернутого дерева (как сортировка по margin в инфоблоках). Добавляет DEPTH_LEVEL -
     * информацию об уровне вложенности.
     *
     * @param array $groups - массив групп, в качестве ключей должны быть указаны id групп (те id, по которым
     * привязываются родительские группы)
     *
     * @param string $parentGroupField - название ключа, по которому записывается id родительской группы
     */
    public static function sortExpandedTree(&$groups, $parentGroupField = 'GROUP_ID')
    {
        $groups = static::getTree($groups, $parentGroupField);

        $expandedGroupsList = array();

        foreach ($groups as $group)
        {
            static::expandGroupRecursive($group, $expandedGroupsList, 1);
        }

        $groups = $expandedGroupsList;
    }

    protected static function expandGroupRecursive($curGroup, &$expandedGroupsList, $depthLevel)
    {
        $curGroup['DEPTH_LEVEL'] = $depthLevel;
        $expandedGroupsList[$curGroup['ID']] = $curGroup;

        if($curGroup['SUBGROUPS'])
        {
            foreach ($curGroup['SUBGROUPS'] as $subsection)
            {
                static::expandGroupRecursive($subsection, $expandedGroupsList, $depthLevel + 1);
            }

            unset($expandedGroupsList[$curGroup['ID']]['SUBGROUPS']);
        }
    }

    /**
     * Вывод дерева
     * @param array $tree - корневые элементы дерева
     * @param string $rootListTemplate - шаблон вывода корневого списка, доступен макрос #LIST_CONTENT#
     * @param string $listTemplate - шаблон вывода промежуточного узла дерева (узел, который является родительским для других узлов).
     * Доступен макрос #LIST_CONTENT# - содержимое дочерних узлов
     * @param string $elementTemplate - шаблон вывода листа дерева (конечного узла)
     * @param string $childFieldName - название ключа узла, в котором хранится массив дочерних узлов
     */
    public static function printTree(array $tree, $rootListTemplate, $listTemplate, $elementTemplate, $childFieldName = 'SUBGROUPS')
    {
        $rootListContent = '';

        foreach ($tree as $node)
        {
            $rootListContent .= static::printTreeNode($node, $listTemplate, $elementTemplate, $childFieldName);
        }

        echo str_replace('#LIST_CONTENT#', $rootListContent, $rootListTemplate);
    }

    protected static function printTreeNode(array $node, $listTemplate, $elementTemplate, $childFieldName)
    {
        $output = '';

        if($node[$childFieldName])
        {
            foreach ($node[$childFieldName] as $subNode)
            {
                $output .= static::printTreeNode($subNode, $listTemplate, $elementTemplate, $childFieldName);
            }

            $template = $listTemplate;
        }
        else
        {
            $template = $elementTemplate;
        }

        if(preg_match_all('/#([A-Z_0-9]+)#/', $template, $matches))
        {
            $replaces = array();

            foreach ($matches[1] as $index => $field)
            {
                $value = '';

                if($field == 'LIST_CONTENT')
                {
                    $value = $output;
                }
                elseif(isset($node[$field]))
                {
                    $value = $node[$field];
                }

                $replaces[$index] = $value;
            }

            $output = str_replace($matches[0], $replaces, $template);
        }

        return $output;
    }

    public static function getFio(array $person, $keyPrefix = '')
    {
        $fio = $person[$keyPrefix . 'LAST_NAME'] ?: '';
        if($person[$keyPrefix . 'NAME'])
        {
            $fio .= ' ' . mb_substr($person[$keyPrefix . 'NAME'], 0 , 1) . '.';
        }

        if($person[$keyPrefix . 'SECOND_NAME'])
        {
            $fio .= ' ' . mb_substr($person[$keyPrefix . 'SECOND_NAME'], 0 , 1) . '.';
        }

        return $fio;
    }

    public static function getShortClassName($className)
    {
        return substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * Переводит строку из snake case в camel case
     * @param string $snakeCaseStr
     * @param bool $bUpper - если true, то верхний camel case, иначе - нижний
     *
     * @return string
     */
    public static function getCamelCase($snakeCaseStr, $bUpper = true)
    {
        $snakeCaseStr = strtolower($snakeCaseStr);

        $regExp = $bUpper ? '/(^|_|-)([a-z])/' : '/(_|-)([a-z])/';

        return preg_replace_callback($regExp, function($matches)
        {
            return strtoupper($matches[2]);
        }, $snakeCaseStr);
    }

    /**
     * Возвращает массив с ключами, переведенными в camelCase
     * @param array $array
     * @param bool $bUpperCase
     *
     * @return array
     */
    public static function camelCaseKeys(array $array, $bUpperCase = true)
    {
        $result = [];
        foreach ($array as $key => $value)
        {
            $result[Helpers::getCamelCase($key, $bUpperCase)] = $value;
        }

        return $result;
    }

    public static function getSnakeCase($camelCaseStr, $bUpper = true, $separator = '_')
    {
        $camelCaseStr[0] = strtolower($camelCaseStr[0]);
        $result = preg_replace('/([A-Z])/', $separator . '$1', $camelCaseStr);

        if($bUpper)
        {
            $result = strtoupper($result);
        }
        else
        {
            $result = strtolower($result);
        }

        return $result;
    }

    /**
     * Возвращает массив с ключами, переведенными в camelCase
     * @param array $array
     * @param bool $bUpper
     * @param string $separator
     *
     * @return array
     */
    public static function snakeCaseKeys(array $array, $bUpper = true, $separator = '_')
    {
        $result = [];
        foreach ($array as $key => $value)
        {
            $result[Helpers::getSnakeCase($key, $bUpper, $separator)] = $value;
        }

        return $result;
    }

    public static function getSnakeCaseArray($array, $bUpper = true, $separator = '_')
    {
        array_walk($array, function(&$item) use ($bUpper, $separator)
        {
            $item = static::getSnakeCase($item, $bUpper, $separator);
        });

        return $array;
    }

    /**
     * Получает гарантированно уникальный id
     * @return string
     */
    public static function uniqid()
    {
        do
        {
            $id = uniqid();
        }
        while(isset(static::$usedIds[$id]));

        static::$usedIds[$id] = true;
        return $id;
    }

    /**
     * Приводит ключи массива к нижнему регистру
     * @param array $array
     *
     * @return array
     */
    public static function strtolowerKeys(array $array)
    {
        $result = [];

        foreach ($array as $key => $value)
        {
            $result[strtolower($key)] = $value;
        }

        return $result;
    }

    /**
     * Приводит ключи массива к нижнему регистру
     * @param array $array
     *
     * @return array
     */
    public static function strtoupperKeys(array $array)
    {
        $result = [];

        foreach ($array as $key => $value)
        {
            $result[strtoupper($key)] = $value;
        }

        return $result;
    }

    public static function getNamespace($className)
    {

        return preg_replace('/\\\\[A-Za-z0-9]+$/', '', $className);
    }

    /**
     * Для всех файлов в указанной паапке вызывает переданный обработчик
     * @param string $directory
     * @param callable $callback - в качестве аргумента получает абсолютный путь к файлу в папке
     * @param bool $bRecursive - включает режим рекурсивного обхода содержимого папки
     */
    public static function scanDir($directory, $callback, $bRecursive = true)
    {
        // Привести каталог в канонизированный абсолютный путь
        $directory=realpath($directory);

        if ($d=opendir($directory))
        {
            while($fname=readdir($d)) {
                if ($fname=='.' || $fname=='..') {
                    continue;
                }

                if ($bRecursive && is_dir($directory.DIRECTORY_SEPARATOR.$fname)) {
                    static::scanDir($directory.DIRECTORY_SEPARATOR.$fname, $callback);
                }
                else
                {
                    // Передать путь файла в callback-функцию
                    if ($callback!=null && is_callable($callback)) {
                        $callback($directory.DIRECTORY_SEPARATOR.$fname);
                    }
                }
            }
            closedir($d);
        }
    }

    public static function preparePhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Возвращает путь относительно корня сайта
     * @param string $absolutePath
     *
     * @return bool|string
     */
    public static function getRelPath($absolutePath)
    {
        if(strpos($absolutePath, $_SERVER['DOCUMENT_ROOT']) === 0)
        {
            $result = substr($absolutePath, strlen($_SERVER['DOCUMENT_ROOT']));
        }
        else
        {
            $result = $absolutePath;
        }

        return $result;
    }

    /**
     * Проверяет, является ли массив ассоциативным
     * @param array $arr
     *
     * @return bool
     */
    public static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}