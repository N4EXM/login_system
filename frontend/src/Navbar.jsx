import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from './context/AuthContext';

const Navbar = () => {

  const { user } = useAuth()

  return (
    <nav
        className='flex flex-row items-center justify-start gap-10  w-full h-fit p-10 px-20 fixed top-0 left-0'
    >
        <h1
            className='font-bold text-xl'
        >
            login system
        </h1>
        <div
          className='flex flex-row items-center justify-start w-full gap-5'
        >
          <Link
            to={"/home"}
          >
            Home
          </Link>
          {user.role === "admin" && 

            <Link
              to={"/AdminPage"}
            >
              Dashboard
            </Link>

          }
        </div>
    </nav>
  )
}

export default Navbar