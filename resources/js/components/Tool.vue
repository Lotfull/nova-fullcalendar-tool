<template>
    <div>
        <heading class="mb-6">Календарь</heading>

        <div style="display: flex">
            <div style="width: 25%">
                <h3 class="text-sm uppercase tracking-wide text-80 bg-30 p-3">Клуб</h3>
                <div class="p-2">
                    <select class="form-control-sm form-select select-w" v-model="club_selected"
                            @change="filter_update">
                        <option value="">Все клубы</option>
                        <option v-for="option in filters" v-bind:value="option">
                            {{ option.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div style="width: 25%">
                <h3 class="text-sm uppercase tracking-wide text-80 bg-30 p-3">Активность</h3>
                <div class="p-2">
                    <select class="form-control-sm form-select select-w" v-model="service_selected"
                            @change="filter_update">
                        <option value="">Все активности</option>
                        <option v-for="option in (club_selected.services || Array.isArray(filters) && filters.flatMap(a => a.services))"
                                v-bind:value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>
            </div>
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
                :min-time="minTime"
                :now-indicator="true"
                :custom-buttons="customButtons"
                :header="{
                left: 'prev,next today createSeanceButton',
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
                ruLocale: ruLocale,
                minTime: "06:00:00",
                customButtons: {
                    createSeanceButton: {
                        text: 'Добавить сеанс',
                        click: this.handleDateClick
                    }
                }
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

    .fc-today {
        background-color: inherit !important;
    }

    .select-w {
        width: 100%;
    }

    .fc-button-primary {
        color: var(--white);
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .fc-button-active {
        background-color: var(--90);
        border-color: var(--90);
    }

    .fc-button {
        border-radius: 0.5rem;
    }
</style>
