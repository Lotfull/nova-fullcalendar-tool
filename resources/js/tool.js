Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'nova-fullcalendar-tool',
            path: '/nova-fullcalendar-tool',
            component: require('./components/Tool'),
        },
    ])
})
