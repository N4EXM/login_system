import { useState } from 'react'
import {BrowserRouter, Routes, Route} from 'react-router-dom'
import RegisterPage from './pages/RegisterPage'
import LoginPage from './pages/LoginPage'
import AdminPage from './pages/adminPage'

function App() {
  const [count, setCount] = useState(0)

  return (
    <>

      <div
        className='w-full bg-slate-800 min-w-screen h-full min-h-screen flex items-center justify-center'  
      >

      <BrowserRouter>
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
            path='/AdminPage'
            element={<AdminPage/>}
          />
        </Routes>
      </BrowserRouter>

      </div>  
    </>
  )
}

export default App
