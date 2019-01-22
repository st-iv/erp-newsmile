<?

namespace Mmit\NewSmile\Search;

interface Searchable
{
    /**
     * Возвращает основную запись в поисковом индексе
     *
     * @param array $fields - значения полей индексируемой записи
     * @return array
     */
    public static function getMainIndex($fields);

    /**
     * Возвращает массив кодов полей, которые составят дополнительные записи в поисковом индексе - значение каждого поля
     * будет проиндексировано отдельно для возможности группировки результатов поиска по полям
     * @return mixed
     */
    public static function getSearchableFields();
}