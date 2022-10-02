import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

import 'view-ui-plus/dist/styles/viewuiplus.css'
import {
    locale
} from 'view-ui-plus';
import lang from 'view-ui-plus/dist/locale/en-US';
import {
    DatePicker
} from 'view-ui-plus';

Nova.booting((app, store) => {
    locale(lang);
    app.component('x-date-picker', DatePicker);
    app.component('index-multiple-date-picker', IndexField)
    app.component('detail-multiple-date-picker', DetailField)
    app.component('form-multiple-date-picker', FormField)
})
