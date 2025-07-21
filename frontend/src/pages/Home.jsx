import React from 'react'
import Navbar from './../Navbar'
import { useAuth } from '../context/AuthContext'

const Home = () => {

  const { user } = useAuth()

  return (
    <div className='relative'>
      <h1>
        Hello 
        {user?.username}
        {user?.role}
      </h1>
      <Navbar></Navbar>
    </div>
  )
}

export default Home