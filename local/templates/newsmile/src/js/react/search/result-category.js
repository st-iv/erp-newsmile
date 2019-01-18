import React from 'react'
import PropTypes from 'prop-types'

export default class ResultCategory extends React.PureComponent
{
    static propTypes = {
        code: PropTypes.string.isRequired,
        subcategories: PropTypes.object
    };

    static defaultProps = {
        subcategories: {}
    };

    static categoriesTitles = {
        patientcard: 'Пациенты',
        doctor: 'Врачи'
    };

    static subcategoriesTitles = {
        patientcard: {
            personal_phone: 'По номеру телефона',
            number: 'По номеру карточки пациента'
        }
    };

    render()
    {
        if(!(this.props.code === 'patientcard')) return null;

        return (
            <div className={'search_res_' + ((this.props.code === 'patientcard') ? 'patients' : 'doctors')}>
                <div className="search_res_title">
                    {this.constructor.categoriesTitles[this.props.code]}
                </div>

                {(this.props.code === 'patientcard')
                    ? this.renderPatientCategory()
                    : this.renderDoctorCategory()
                }
            </div>
        );
    }

    renderPatientCategory()
    {
        return (
            <div className="search_res_patients_с">
                {General.mapObj(this.props.subcategories, (items, subcategoryCode) =>
                {
                    const isMainSubcat = (subcategoryCode === 'main');
                    const subcatsTitles = this.constructor.subcategoriesTitles[this.props.code];
                    const subtitle = subcatsTitles && subcatsTitles[subcategoryCode];

                    return (
                        <div className="search_res_list" key={subcategoryCode}>
                            {subtitle && (
                                <div className="search_res_subtitle">
                                    {subtitle} <span>{Object.keys(items).length}</span>
                                </div>
                            )}

                            <div className="search_res_cont">
                                {General.mapObj(items, item =>
                                {
                                    const searchEntry = this.prepareSearchEntry(item.searchEntry);

                                    return (
                                        <div className="search_res_item" key={item.id}>
                                            <div className="search_item_fl">
                                                <div className="search_item_name">
                                                    {isMainSubcat ? searchEntry : General.getFio(item)}
                                                </div>
                                                <div className="search_item_age">
                                                    {General.Date.getAge(item.personalBirthday)}
                                                </div>
                                            </div>

                                            {!isMainSubcat && (
                                                <div className="search_item_phone">
                                                    {searchEntry}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    )
                })}
            </div>
        );
    }

    renderDoctorCategory()
    {
        return (
            <div className="search_res_list">
                <div className="search_res_cont">
                </div>
            </div>
        );
    }

    /**
     * Сервер выделяет найденные вхождения поисковой строки тегом b. Этот метод подготавливает строку для вывода - оборачивает вхождения
     * в span и возвращает массив
     * @param searchEntry
     * @returns {*}
     */
    prepareSearchEntry(searchEntry)
    {
        let startIndexes = this.indexOfAll(searchEntry, '<b>');
        let endIndexes = this.indexOfAll(searchEntry, '</b>');

        if(!startIndexes.length) return searchEntry;

        let result = [searchEntry.substr(0, startIndexes[0])];

        startIndexes.forEach((startIndex, index) =>
        {
            if(endIndexes[index] === undefined) return;
            const cleanEntry = searchEntry.substr(startIndex + 3, endIndexes[index] - startIndex - 3);

            result.push(<span key={index}>{cleanEntry}</span>);
        });

        result.push(searchEntry.substr(endIndexes.pop() + 4));

        return result;
    }

    indexOfAll(string, entry)
    {
        let result = [];
        let entryPos = -1;

        do
        {
            entryPos = string.indexOf(entry, entryPos + 1);

            if(entryPos !== -1)
            {
                result.push(entryPos);
            }
        }
        while(entryPos !== -1);

        return result;
    }
}