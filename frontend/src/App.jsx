import { useState } from 'react'
import {BrowserRouter, Routes, Route} from 'react-router-dom'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import LoginPage from './pages/LoginPage'

function App() {
  const [count, setCount] = useState(0)

  return (
    <>

      <div
        className='w-full bg-slate-800 min-w-screen h-full min-h-screen'  
      >

      <BrowserRouter>
        <Routes>
          <Route
            path='/'
            element={<LoginPage/>}
          />
        </Routes>
      </BrowserRouter>

      </div>  
    </>
  )
}

export default App
