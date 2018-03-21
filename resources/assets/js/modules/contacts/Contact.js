import Model from '../../utils/Model'
import User from '../../modules/user/User'
import Company from '../../modules/companies/Company'
import store from '../../store'
import { getCustomFieldsForContacts } from './store/selectors'
import { getCustomFieldValue } from '../../utils/helpers/customFieldsHelper'
import Note from "../notes/Note"
import Opportunity from "../opportunities/Opportunity"
import _ from 'lodash'

class Contact extends Model {
  constructor(props) {
    super(props)

    this.initialize(props)
  }

  initialize(props) {
    props.custom_fields = props.custom_fields ? props.custom_fields : []

    const fields = getCustomFieldsForContacts(store.getState())

    Object.keys(fields).map(key => {
        const field = fields[key]

        if (field.is_custom) {
            this[key] = getCustomFieldValue(field.alias, props.custom_fields, field.default)
        } else {
            if (! props.hasOwnProperty(key)) {
                props[key] = null
            }
            
            this[key] = props[key]
        }
    })

    super.initialize(props)

    // relate user model
    this.user = props.user ? new User(props.user) : new User({})
    this.companies = props.companies && props.companies.map(c => new Company(c)) || []
    this.notes = props.notes && props.notes.map(n => new Note(n)) || []
    this.opportunities = props.opportunities && props.opportunities.map(d => new Opportunity(d)) || []
    this.activities = props.activities || []
    this.name = `${props.first_name} ${props.last_name}`
    this.pivot = props.pivot ? props.pivot : {}

    const primaryCompany = _.find(this.companies, c => c.primary === 1)

    this.company = typeof primaryCompany === 'undefined' ? new Company({}) : primaryCompany
  }
}

export default Contact
