import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

import 'v-calendar/style.css'
import { setupCalendar } from 'v-calendar'

Nova.booting((app, store) => {
    app.use(setupCalendar, {})
    app.component('index-multiple-date-picker', IndexField)
    app.component('detail-multiple-date-picker', DetailField)
    app.component('form-multiple-date-picker', FormField)
})
