import { useState } from 'react'
import {BrowserRouter, Routes, Route} from 'react-router-dom'
import RegisterPage from './pages/RegisterPage'
import LoginPage from './pages/LoginPage'
import AdminPage from './pages/adminPage'
import Home from './pages/Home'
import RoleProtectedRoute from './RoleProtectedRoute'
import { AuthProvider } from './context/AuthContext'

function App() {

  return (
    <>

      <div
        className='w-full bg-slate-800 min-w-screen h-full min-h-screen flex items-center justify-center'  
      >
        
          <BrowserRouter>
            <AuthProvider>
              <Routes>
                <Route
                  path='/'
                  element={<LoginPage/>}
                />
                <Route
                  path='/Register'
                  element={<RegisterPage/>}
                />
                <Route
                  path='/Home'
                  element={<Home/>}
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
            </AuthProvider>
          </BrowserRouter>
        
      </div>  
    </>
  )
}

export default App
