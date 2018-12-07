import React from 'react'
import Select from './select'
import ColoredSelect from './colored-select'

class Filter extends React.Component
{
    specializations = this.getSpecializationOptions();

    render()
    {
        const doctors = this.props.doctors.map(doctor =>
        {
            return {
                value: doctor.id,
                label: doctor.fio,
                color: doctor.color
            };
        });

        console.log(doctors);

        return (
            <form>
                <div className="row shld_filter">
                    <div className="shld_filter_title">Расписание</div>
                    <Select options={this.specializations}
                            className="specializations-select"
                            placeholder="Профессия"
                            onChange={value => console.log('new value!', value)}
                    />

                    <ColoredSelect options={doctors}
                            className="doctors-select"
                            placeholder="Врач"/>
                </div>
            </form>
        )
    }

    renderDoctor

    getSpecializationOptions()
    {
        let result = [];
        let specializationsCodes = {};

        this.props.doctors.forEach(doctor =>
        {
            if(!specializationsCodes[doctor.specialization_code])
            {
                result.push({
                    label: doctor.specialization,
                    value: doctor.specialization_code
                });
            }
        });

        return result;
    }
}

export default Filter