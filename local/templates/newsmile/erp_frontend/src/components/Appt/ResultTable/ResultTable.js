import React from 'react'
import './ResultTable.scss'

export default class ResultTable extends React.Component {
    render() {
        return (
            <div className="result-table">
				<div className="result-table__row result-table__row--header">
					<div class="result-table__cell result-table__cell--title">
						Операция
					</div>
					<div class="result-table__cell result-table__cell--teeth">
						Зубы
					</div>
					<div class="result-table__cell result-table__cell--price">
						Цена за ед. &#8381;
					</div>
					<div class="result-table__cell result-table__cell--quantity">
						Количество
					</div>
					<div class="result-table__cell result-table__cell--cost">
						Стоимость (мин-макс) &#8381;
					</div>
				</div>
				<div className="result-table__row result-table__row--total">
					<div class="result-table__cell result-table__cell--name">
						Итого
					</div>
					<div class="result-table__cell result-table__cell--cost">
						34 830 - 35 330
					</div>
				</div>
				<div className="result-table__row result-table__row--service">
					<div class="result-table__cell result-table__cell--name">
						Имплантология
					</div>
					<div class="result-table__cell result-table__cell--cost">
						17 430 - 17 930
					</div>
				</div>
				<div className="result-table__row">
					<div class="result-table__cell result-table__cell--title">
						Установка импланта системы ADIN Touareg-S (ADIN, Израиль)
					</div>
					<div class="result-table__cell result-table__cell--teeth">
						<span className="result-table__tooth-num">46</span>
						<span className="result-table__tooth-num">47</span>
					</div>
					<div class="result-table__cell result-table__cell--price">
						17 430 - 17 930
					</div>
					<div class="result-table__cell result-table__cell--quantity">
						<span className="result-table__service-num">1</span>
					</div>
					<div class="result-table__cell result-table__cell--cost">
						17 430 - 17 930
					</div>
				</div>
				<div className="result-table__row result-table__row--service">
					<div class="result-table__cell result-table__cell--name">
						Ортопедия
					</div>
					<div class="result-table__cell result-table__cell--cost">
						17 400
					</div>
				</div>
				<div className="result-table__row">
					<div class="result-table__cell result-table__cell--title">
						Абатмент системы MIS M4/Seven
					</div>
					<div class="result-table__cell result-table__cell--teeth">
						<span className="result-table__tooth-num">н.ч.</span>
						<span className="result-table__tooth-num">42</span>
						<span className="result-table__tooth-num">46</span>
					</div>
					<div class="result-table__cell result-table__cell--price">
						8 700
					</div>
					<div class="result-table__cell result-table__cell--quantity">
						<span className="result-table__service-num">2</span>
					</div>
					<div class="result-table__cell result-table__cell--cost">
						17 400
					</div>
				</div>
				<div className="result-table__row">
					<div class="result-table__cell result-table__cell--title">
						Установка мемецкого крепления G-dent PN-5
					</div>
					<div class="result-table__cell result-table__cell--teeth">
						<span className="result-table__tooth-num">н.ч.</span>
					</div>
					<div class="result-table__cell result-table__cell--price">
						8 700
					</div>
					<div class="result-table__cell result-table__cell--quantity">
						<span className="result-table__service-num">2</span>
					</div>
					<div class="result-table__cell result-table__cell--cost">
						17 400
					</div>
				</div>
			</div>
        )
    }
}