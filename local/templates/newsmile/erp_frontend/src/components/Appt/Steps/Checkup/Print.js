import React from 'react'
import {PrinterImg} from "./../../../common/Icons"
import Button from '../../../common/Button/Button';
import Tag from '../../../common/Tags/Tag';
import "./Print.scss"

export default class Print extends React.Component {
	render() {
		return (
			<div className="print">
				<div className="print__img">
					<PrinterImg />
				</div>
				<div className="print__content">
					<div class="print__info">
						<p className="print__header">Печать <span className="print__header-num">4</span> документов</p>
						<ul className="print__list">
							<li className="print__item">
								<span className="print__item-num">1</span>
								<span className="print__item-title">Результаты осмотра</span>
								<Tag text="На подпись" variant="outline--mango"/>
								<Tag text="Выдать" variant="outline--grape"/>
							</li>
							<li className="print__item">
								<span className="print__item-num">1</span>
								<span className="print__item-title">Результаты осмотра</span>
								<Tag text="Вклеить в карту" variant="outline--success"/>
							</li>
							<li className="print__item">
								<span className="print__item-num">1</span>
								<span className="print__item-title">Зубная формула</span>
								<Tag text="На подпись" variant="outline--mango"/>
								<Tag text="Выдать" variant="outline--grape"/>
							</li>
							<li className="print__item">
								<span className="print__item-num">1</span>
								<span className="print__item-title">Зубная формула</span>
								<Tag text="Вклеить в карту" variant="outline--success"/>
							</li>
						</ul>
					</div>
					<div className="print__btns">
						<div className="print__finish">
							<Button variant="success" text="Распечатать"/>
							<p className="print__warning">Необходимо включить принтер и проверить наличие бумаги</p>
						</div>
						<Button text="Документы подготовлены, продолжить" action="next" variant="disabled"/>
					</div>
				</div>
			</div>
		)
	}
}