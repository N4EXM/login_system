import { useState } from 'react'
import {BrowserRouter, Routes, Route} from 'react-router-dom'
import RegisterPage from './pages/RegisterPage'
import LoginPage from './pages/LoginPage'
import AdminPage from './pages/adminPage'
import RoleProtectedRoute from './RoleProtectedRoute'

function App() {

  return (
    <>

      <div
        className='w-full bg-slate-800 min-w-screen h-full min-h-screen flex items-center justify-center'  
      >
        <Routes>
          <Route
            path='/'
            element={<LoginPage/>}
          />
          <Route
            path='/Register'
            element={<RegisterPage/>}
          />
          
          {/* protected routes */}
          <Route 
            element={<RoleProtectedRoute allowedRoles={["admin"]}/>}
            
          >
            <Route
              path='/AdminPage'
              element={<AdminPage/>}
            />
          </Route>  
        </Routes>
      </div>  
    </>
  )
}

export default App
