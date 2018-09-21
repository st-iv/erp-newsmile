<?

namespace Mmit\NewSmile;

class Helpers
{
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
}