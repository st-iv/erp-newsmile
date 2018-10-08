<?

namespace Mmit\NewSmile;

class DependencesManager
{
    public static function getPullDependenceData()
    {
        return Array(
            'MODULE_ID' => 'mmit.newsmile',
            'USE' => Array('PUBLIC_SECTION')
        );
    }
}