import Vue from 'vue'
import Router from 'vue-router'
import Login from '@/components/Login'
import Logout from '@/components/Logout'
import Register from '@/components/Register'
import Dashboard from '@/components/Dashboard'
import Pages from '@/components/Pages'
import Categories from '@/components/Categories'
import Widgets from '@/components/Widgets'
import Settings from '@/components/Settings'
import Meta from '@/components/Meta'
import Category from '@/components/Category'
import Images from '@/components/Images'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Dashboard',
      component: Dashboard
    },
    {
      path: '/Login',
      name: 'Login',
      component: Login
    },
    {
      path: '/Logout',
      name: 'Logout',
      component: Logout
    },
    {
      path: '/Register',
      name: 'Register',
      component: Register
    },
    {
      path: '/Pages',
      name: 'Pages',
      component: Pages
    },
    {
      path: '/Categories',
      name: 'Categories',
      component: Categories
    },
    {
      path: '/Widgets',
      name: 'Widgets',
      component: Widgets
    },
    {
      path: '/Settings',
      name: 'Settings',
      component: Settings
    },
    {
      path: '/Meta',
      name: 'Meta',
      component: Meta
    },
    {
      path: '/Category',
      name: 'Category',
      component: Category
    },
    {
      path: '/Images',
      name: 'Images',
      component: Images
    }
  ]
})
