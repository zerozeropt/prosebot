import React from 'react'
import CIcon from '@coreui/icons-react'
import {
  cilInput,
  cilFootball
} from '@coreui/icons'
import { CNavItem } from '@coreui/react'

const _nav = [
  {
    component: CNavItem,
    name: 'Dashboard',
    to: '/dashboard',
    icon: <CIcon icon={cilFootball} customClassName="nav-icon" />
  },
  {
    component: CNavItem,
    name: 'Import',
    to: '/import',
    icon: <CIcon icon={cilInput} customClassName="nav-icon" />
  }
]

export default _nav
