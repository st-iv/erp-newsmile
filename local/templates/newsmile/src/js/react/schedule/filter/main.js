import React from 'react'
import Select from './select'
import ColoredSelect from './colored-select'

class Filter extends React.Component
{
    specializations = this.getSpecializationOptions();
    doctors = this.getDoctors();

    state = this.getInitialState();
    submittedFilter = this.getFilterConfig(this.state);

    static defaultProps =  {
        defaultDoctor: 0,
        defaultSpecialization: ''
    };

    render()
    {
        return (
            <form>
                <div className="row shld_filter">
                    <div className="shld_filter_title">Расписание</div>
                    <Select options={this.specializations}
                            className="specializations-select"
                            placeholder="Профессия"
                            value={this.state.specialization}
                            onChange={option => this.setState({specialization: option})}
                    />

                    <ColoredSelect options={this.doctors}
                            className="doctors-select"
                            placeholder="Врач"
                            value={this.state.doctor}
                            onChange={option => this.setState({doctor: option})}
                    />

                    {!this.isInitialState() && (
                        <input className="shld_btn_dcl" type="reset" value="" onClick={this.reset.bind(this)}/>
                    )}

                    {!this.isSubmitted() && (
                        <input className="shld_btn_acc" type="submit" value="" onClick={this.submit.bind(this)}/>
                    )}
                </div>
            </form>
        )
    }

    getSpecializationOptions()
    {
        let result = [];
        let specializationsCodes = {};

        this.props.doctors.forEach(doctor =>
        {
            if(doctor.specialization_code && !specializationsCodes[doctor.specialization_code])
            {
                result.push({
                    label: doctor.specialization,
                    value: doctor.specialization_code
                });

                specializationsCodes[doctor.specialization_code] = true;
            }
        });

        result.unshift({
            label: 'Профессия',
            value: ''
        });

        return result;
    }

    getDoctors()
    {
        let doctors = this.props.doctors.map(doctor =>
        {
            return {
                value: doctor.id,
                label: doctor.fio,
                color: doctor.color
            };
        });

        doctors.unshift({
            value: 0,
            label: 'Врач',
            color: '#fff'
        });

        return doctors;
    }

    getFilterConfig(state)
    {
        return {
            doctor: state.doctor.value,
            specialization: state.specialization.value
        };
    }

    submit(e, filter = null)
    {
        e && e.preventDefault();

        filter = filter ? filter : this.getFilterConfig(this.state);
        if(this.isSubmitted(filter)) return;

        this.props.setFilter(filter);
        this.submittedFilter = filter;
    }

    reset()
    {
        let initialState = this.getInitialState();
        this.setState(initialState);
        this.submit(null, this.getFilterConfig(initialState));
    }

    getInitialState()
    {
        return {
            doctor: this.getOptionByValue(this.doctors, this.props.defaultDoctor),
            specialization: this.getOptionByValue(this.specializations, this.props.defaultSpecialization)
        };
    }

    isInitialState()
    {
        return JSON.stringify(this.state) === JSON.stringify(this.getInitialState());
    }

    isSubmitted(filter = null)
    {
        if(!filter)
        {
            filter = this.getFilterConfig(this.state);
        }

        return JSON.stringify(this.submittedFilter) === JSON.stringify(filter);
    }

    getOptionByValue(options, value)
    {
        let result = null;

        options.forEach(option =>
        {
            if(option.value === value)
            {
                result = option;
            }
        });

        return result;
    }
}

export default Filter