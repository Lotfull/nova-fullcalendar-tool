<template>
    <div>
        <heading class="mb-6">Календарь</heading>

        <div class="mb-6">
            <span>Клуб: </span>
            <select v-model="club_selected" @change="filter_update">
                <option value="">Все клубы</option>
                <option v-for="option in filters" v-bind:value="option">
                    {{ option.name }}
                </option>
            </select>

            <span style="margin-left: 1em">Активность: </span>
            <select v-model="service_selected" @change="filter_update">
                <option value="">Все активности</option>
                <option v-for="option in (club_selected.services || filters.flatMap(a => a.services))"
                        v-bind:value="option.id">
                    {{ option.name }}
                </option>
            </select>

<!--            <button @click="club_selected = ''; service_selected = ''">Сбросить фильтр</button>-->
        </div>

        <FullCalendar
                @dateClick="handleDateClick"
                @eventClick="handleEventClick"
                @eventRender="handleEventRender"
                default-view="timeGridWeek"
                ref="fullCalendar"
                :plugins="calendarPlugins"
                :weekends="calendarWeekends"
                :events="calendarEvents"
                :locale="ruLocale"
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
    import ruLocale from '@fullcalendar/core/locales/ru';
    import Tooltip from 'tooltip.js';

    let seances_request_url = '/v1/calendar/seances';
    let filters_request_url = '/v1/calendar/filters';

    export default {
        components: {
            FullCalendar
        },
        methods: {
            filter_update() {
                this.set_events()
            },
            // filter_reset() {
            //     this.selected_club = '';
            //     this.selected_service = '';
            // },
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
                    if (response.data)
                        this.calendarEvents = response.data;
                });
            },
            handleDateClick(info) {
                if (this.service_selected) {
                    window.open(`/resources/seances/new?viaResource=services&viaResourceId=${this.service_selected}&viaRelationship=seances`, "_blank");
                } else {
                    window.open(`/resources/seances/new`, "_blank");
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
                calendarEvents: [],
                ruLocale: ruLocale
            };
        },
        mounted() {
            this.set_events();
            this.fetch_filters().then(response => {
                this.filters = response.data;
            });
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
