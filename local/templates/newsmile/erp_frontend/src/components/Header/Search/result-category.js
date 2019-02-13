import React from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import MCustomScrollbar from './../../../common/ui/m-custom-scrollbar'
import DateHelper from './../../../common/helpers/date-helper'
import GeneralHelper from './../../../common/helpers/general-helper.js';
import PhoneHelper from './../../../common/helpers/phone-helper.js';
import StringHelper from './../../../common/helpers/string-helper.js';

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
        return (
            <div className="search_res">
                <div className="search_res_title">
                    {this.constructor.categoriesTitles[this.props.code]}
                    {}
                    <span>{this.getItemsCount()}</span>
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
                {GeneralHelper.mapObj(this.props.subcategories, (items, subcategoryCode) =>
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

                            <MCustomScrollbar className="search_res_cont">
                                {GeneralHelper.mapObj(items, item =>
                                {
                                    const preparedSearchEntry = this.prepareSearchEntry(item.searchEntry);

                                    return (
                                        <div className="search_res_item" key={item.id}>
                                            <div className="search_item_fl">
                                                <div className="search_item_name">
                                                    {isMainSubcat ? preparedSearchEntry : GeneralHelper.getFio(item)}
                                                </div>

                                                {item.personalBirthday && (
                                                    <div className="search_item_age">
                                                        {DateHelper.getAge(item.personalBirthday)}
                                                    </div>
                                                )}
                                            </div>

                                            {!isMainSubcat && (
                                                <div className="search_item_phone">
                                                    {(subcategoryCode === 'personal_phone')
                                                        ? this.preparePhoneEntry(item.searchEntry)
                                                        : preparedSearchEntry
                                                    }
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                            </MCustomScrollbar>
                        </div>
                    )
                })}
            </div>
        );
    }

    renderDoctorCategory()
    {
        let items = this.uniteSubcategories(this.props.subcategories);

        return (
            <div className="search_res_list">
                <MCustomScrollbar className="search_res_cont">
                    {GeneralHelper.mapObj(items, item =>
                    {
                        return (
                            <div className="search_res_item_doct" key={item.id}>
                                <div className="search_item_dname" style={{backgroundColor: item.color}}>
                                    {(item.entries.main && this.prepareSearchEntry(item.entries.main)) || GeneralHelper.getFio(item)}
                                </div>

                                <div className="search_item_dage">
                                    {DateHelper.getAge(item.personalBirthday)}
                                </div>

                                {item.entries.personal_phone && (
                                    <div className="search_item_dphone">
                                        {this.preparePhoneEntry(item.entries.personal_phone)}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </MCustomScrollbar>
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
        let $searchEntry = $('<div>' + searchEntry + '</div>');
        let result = [];

        $searchEntry.contents().each((index, entryNode) =>
        {
            let $entryNode = $(entryNode);
            if(entryNode.tagName === 'B')
            {
                result.push(<span key={index}>{$entryNode.text()}</span>);
            }
            else
            {
                result.push($entryNode.text());
            }
        });

        return result;
    }

    preparePhoneEntry(searchEntry)
    {
        let regexp = /<b>([^<]*)<\/b>/ig;
        let match;

        let rawPhone = searchEntry.replace('<b>', '');
        rawPhone = rawPhone.replace('</b>', '');
        let formattedPhone = PhoneHelper.format(rawPhone);


        while(match = regexp.exec(searchEntry))
        {
            formattedPhone = StringHelper.insert('<b>', formattedPhone, PhoneHelper.getNumFormattedPos(match.index));
            formattedPhone = StringHelper.insert('</b>', formattedPhone, PhoneHelper.getNumFormattedPos(match.index + match[1].length - 1) + 4);
        }

        return this.prepareSearchEntry(formattedPhone);
    }

    uniteSubcategories(subcategories)
    {
        let result = {};
        subcategories = GeneralHelper.clone(subcategories);

        GeneralHelper.forEachObj(subcategories, (subcategoryItems, subcategoryCode) =>
        {
            GeneralHelper.forEachObj(subcategoryItems, item =>
            {
                if(!(item.id in result))
                {
                    item.entries = {};
                    result[item.id] = item;
                }

                result[item.id].entries[subcategoryCode] = item.searchEntry;
            });
        });

        return result;
    }

    getItemsCount()
    {
        let ids = {};

        GeneralHelper.forEachObj(this.props.subcategories, items =>
        {
            GeneralHelper.forEachObj(items, item =>
            {
                ids[item.id] = true;
            })
        });

        return Object.keys(ids).length;
    }
}