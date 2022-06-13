import React from 'react'

const Dashboard = React.lazy(() => import('./views/pages/Dashboard'));
const ImportTemplate = React.lazy(() => import('./views/pages/ImportTemplate'))

const routes = [
  { path: '/', exact: true, name: 'Home' },
  { path: '/dashboard', name: 'Dashboard', element: Dashboard },
  { path: '/import', name: 'ImportTemplate', element: ImportTemplate }
]

export default routes
