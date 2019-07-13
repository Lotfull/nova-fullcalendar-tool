<template>
    <div>
        <heading class="mb-6">Nova Fullcalendar Tool</heading>

        <span>Клуб: </span>
        <select v-model="club_selected" @input="filter_update">
            <option value=""></option>
            <option v-for="option in filters" v-bind:value="option">
                {{ option.name }}
            </option>
        </select>

        <span>Услуга: </span>
        <select v-model="service_selected" @input="filter_update">
            <option value=""></option>
            <option v-for="option in (club_selected.services || filters.flatMap(a => a.services))" v-bind:value="option.id">
                {{ option.name }}
            </option>
        </select>

        <button @click="filter_reset">Сбросить фильтр</button>

        <FullCalendar
                @dateClick="handleDateClick"
                @eventClick="handleEventClick"
                @eventRender="handleEventRender"
                default-view="timeGridWeek"
                ref="fullCalendar"
                :plugins="calendarPlugins"
                :weekends="calendarWeekends"
                :events="calendarEvents"
                :header="{
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            }"
        />
    </div>
</template>

<script>
    import FullCalendar from "@fullcalendar/vue";
    import dayGridPlugin from "@fullcalendar/daygrid";
    import timeGridPlugin from "@fullcalendar/timegrid";
    import interactionPlugin from "@fullcalendar/interaction";
    import Tooltip from 'tooltip.js';

    let seance_link = seance => '/crm/resources/seances/' + seance.id;
    let seances_request_url = '/v1/calendar/seances';
    let filters_request_url = '/v1/calendar/filters';

    let ru_translation = {
        'first_name': 'Имя',
        'last_name': 'Фамилия',
        'mid_name': 'Отчество',
        'phone': 'Номер телефона',
        'email': 'Email',
        'birthdate': 'Дата рождения',
        'sex': 'Пол',
        'passport_id': 'Номер паспорта',
        'issue_date': 'Дата выдачи паспорта',
        'issued_by': 'Орган выдачи паспорта',
        'driving_license': 'Номер водительского удостоверения',
        'driving_exp': 'Водительский стаж',
        'driving_cat': 'Водительские категории',
        'height': 'Рост',
        'weight': 'Вес',
        'clothing_size': 'Размер одежды',
        'shoe_size': 'Размер обуви',
    };

    let prepare_client_data = client_data_json => {
        let result = '';
        for (let a in client_data_json) {
            if (!!client_data_json[a])
                result += `<p>${ru_translation[a]}: ${client_data_json[a]}</p>`;
        }
        return result
    };

    let calendar_event_from_seance = seance => {
        let time = moment(seance.time, ['hh:mm:ss']);
        let start = moment(seance.date).set({
            hour: time.get('hour'),
            minute: time.get('minute')
        });
        let end = moment(start).add({
            minute: seance.duration
        });
        return {
            title: seance.description,
            start: start.format(),
            end: end.format(),
            extendedProps: {
                description: seance.description,
                price: seance.price,
                duration: seance.duration,
                service: seance.service.name,
                client: prepare_client_data(seance.clients[0])
            },
            url: seance_link(seance)
        };
    };

    export default {
        components: {
            FullCalendar
        },
        methods: {
            filter_update() {
                this.set_events()
            },
            filter_reset() {
                this.selected_club = '';
                this.selected_service = '';
            },
            fetch_seances() {
                let query = seances_request_url;
                if (this.service_selected)
                    query += `?service=${this.service_selected}`;
                else if (this.club_selected)
                    query += `?club=${this.club_selected.id}`;

                return axios.get(query)
            },
            fetch_filters() {
                return axios.get(filters_request_url)
            },
            set_events() {
                this.fetch_seances().then(response => {
                    let seances = response.data;
                    if (seances) {
                        this.calendarEvents = seances.map(calendar_event_from_seance);
                    }
                });
            },
            handleDateClick(info) {
                if (this.service_selected) {
                    window.open(`/crm/resources/seances/new?viaResource=services&viaResourceId=${this.service_selected}&viaRelationship=seances`, "_blank");
                } else {
                    window.open(`/crm/resources/seances/new`, "_blank");
                }
            },
            handleEventClick(info) {
                console.log('handleEventClick', info.url);
                if (info.url) {
                    window.open(info.url, "_blank");
                    return false;
                }
            },
            handleEventRender(info) {
                new Tooltip(info.el, {
                    html: true,
                    title: `<h4>${info.event.extendedProps.service}</h4>` +
                        `<p>${info.event.extendedProps.description}</p>` +
                        `<p>${info.event.extendedProps.price} руб.</p>` +
                        `<p>${info.event.extendedProps.duration} минут</p>` +
                        `<p>${info.event.extendedProps.client}</p>`,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
        },
        data() {
            return {
                club_selected: '',
                service_selected: '',
                filters: {},
                calendarPlugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin
                ],
                calendarWeekends: true,
                calendarEvents: []
            };
        },
        mounted() {
            this.fetch_filters().then(response => {
                this.filters = response.data;
            });
            this.set_events();
        },
    }

</script>

<style>
    @import "~@fullcalendar/core/main.css";
    @import "~@fullcalendar/daygrid/main.css";
    @import "~@fullcalendar/timegrid/main.css";

    .tooltip {
        z-index: 1;
        width: 250px;
        color: #252d37;
        background-color: #ffffff;
        border-radius: 3px;
        border: 1px solid #3788d8;
        padding: 5px;
    }
</style>
