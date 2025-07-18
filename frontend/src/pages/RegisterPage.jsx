import React from 'react'
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

const RegisterPage = () => {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [showPassword, setShowPassword] = useState(false);
  const [userName, setUserName] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  const handleRegisterSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError("");

    if (password !== confirmPassword) {
      setError("Password does not match");
      setIsLoading(false);
      return;
    }

    if (password.length < 8) {
      setError("Password must be at least 8 characters");
      setIsLoading(false);
      return;
    }

    try {
      const response = await fetch("http://localhost:3000/backend/api.php?action=register", {
        method: "POST",
        // credentials: "include",
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          username: userName,
          password: password
        })
      });

      // const data = await response.json();
      // console.log('Registration response:', data);

      // if (!response.ok) {
      //   throw new Error(data.message || 'Registration failed');
      // }

      // if (data.success) {
      //   // Automatically log in after successful registration
      //   const loginSuccess = await login(userName, password);
      //   if (!loginSuccess) {
      //     navigate('/login'); // Redirect to login if auto-login fails
      //   }
      // } else {
      //   setError(data.message || 'Registration failed');
      // }
    
      // First check if we got any response at all
      if (!response) {
        throw new Error('No response from server');
      }

      // Get response text first for debugging
      const responseText = await response.text();
      console.log('Raw response:', responseText); // Debugging

      // Then parse as JSON
      const data = responseText ? JSON.parse(responseText) : null;

      // Check if parsing succeeded
      if (!data) {
        throw new Error('Invalid server response');
      }

      // Now safely check the success property
      if (!data.success) {
        throw new Error(data.message || 'Registration failed');
      }

      // Registration successful
      // You might want to automatically log the user in here
      const loginResponse = await loginUser(userName, password);
      if (loginResponse.success) {
        navigate('/Home'); // Redirect to protected page
      }

    } 
    catch (error) {
      console.error('Registration error:', error);
      setError(error.message || 'Failed to complete registration');
    } 
    finally {
      setIsLoading(false);
    }
  };

  return (
    // page
    <div
      className='flex items-center justify-center w-full h-full p-8 text-slate-100 font-poppins'
    >
      
      {/* register form */}
      <form
        onSubmit={(e) => handleRegisterSubmit(e)}
        className='flex flex-col gap-5 p-5 border border-slate-600 w-full rounded-md text-xs bg-slate-900 md:w-80'
      >
        
        {/* title */}
        <div
          className='flex flex-col gap-4 items-center justify-center'
        >
          <h1
            className='text-3xl font-bold'
          >
            Sign Up
          </h1>  
        </div>

        {/* input fields */}
        <div
          className='flex flex-col gap-5'
        >
          
          <div
            className='flex flex-col gap-2'
          >
            <p
              className='font-medium'
            >
              Name:
            </p>  
            <div
              className='relative'
            >
              <input 
                onChange={(e) => setUserName(e.target.value)}
                value={userName}
                placeholder='Enter your username'
                type="text"
                className='p-2 pl-8 outline-none border w-full rounded-sm bg-slate-800 border-slate-600' 
              />
              <svg className='top-2.5 left-2.5 absolute' xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="currentColor" d="M7.5 6.5C7.5 8.981 9.519 11 12 11s4.5-2.019 4.5-4.5S14.481 2 12 2S7.5 4.019 7.5 6.5M20 21h1v-1c0-3.859-3.141-7-7-7h-4c-3.86 0-7 3.141-7 7v1z" />
              </svg>
            </div>
            
          </div>

          <div
            className='flex flex-col gap-2'
          >
            <p
              className='font-medium'
            >
              Password:
            </p>  
            <div
              className='relative'
            >
              <input 
                onChange={(e) => setPassword(e.target.value)}
                value={password}
                placeholder='Enter your password'
                type={showPassword ? "text" : "password"}
                className='p-2 pl-8 outline-none border w-full rounded-sm bg-slate-800 border-slate-600' 
              />
              <svg className='top-2.5 left-2.5 absolute' xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h1V6a5 5 0 0 1 5-5a5 5 0 0 1 5 5v2zm-6-5a3 3 0 0 0-3 3v2h6V6a3 3 0 0 0-3-3" />
              </svg>
              <span
                onClick={() => setShowPassword(!showPassword)}
                className='absolute top-2.5 right-2.5'
              >
                {showPassword ? 

                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <g fill="none">
                      <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                      <path fill="currentColor" d="M12 4c2.787 0 5.263 1.257 7.026 2.813c.885.781 1.614 1.658 2.128 2.531c.505.857.846 1.786.846 2.656s-.34 1.799-.846 2.656c-.514.873-1.243 1.75-2.128 2.531C17.263 18.743 14.786 20 12 20c-2.787 0-5.263-1.257-7.026-2.813c-.885-.781-1.614-1.658-2.128-2.531C2.34 13.799 2 12.87 2 12s.34-1.799.846-2.656c.514-.873 1.243-1.75 2.128-2.531C6.737 5.257 9.214 4 12 4m0 2c-2.184 0-4.208.993-5.702 2.312c-.744.656-1.332 1.373-1.729 2.047C4.163 11.049 4 11.62 4 12s.163.951.569 1.641c.397.674.985 1.39 1.729 2.047C7.792 17.007 9.816 18 12 18s4.208-.993 5.702-2.312c.744-.657 1.332-1.373 1.729-2.047c.406-.69.569-1.261.569-1.641s-.163-.951-.569-1.641c-.397-.674-.985-1.39-1.729-2.047C16.208 6.993 14.184 6 12 6m0 3q.132 0 .261.011a2 2 0 0 0 2.728 2.728A3 3 0 1 1 12 9" />
                    </g>
                  </svg>
                : 
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                      <path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" />
                      <path d="M16.681 16.673A8.7 8.7 0 0 1 12 18q-5.4 0-9-6q1.908-3.18 4.32-4.674m2.86-1.146A9 9 0 0 1 12 6q5.4 0 9 6q-1 1.665-2.138 2.87M3 3l18 18" />
                    </g>
                  </svg>
                  
              }
              </span>
            </div>  
          </div>

          <div
            className='flex flex-col gap-2'
          >
            <p
              className='font-medium'
            >
              Confirm password:
            </p>  
            <div
              className='relative'
            >
              <input 
                onChange={(e) => setConfirmPassword(e.target.value)}
                placeholder='Confirm your password'
                value={confirmPassword}
                type={showPassword ? "text" : "password"}
                className='p-2 pl-8 outline-none border w-full rounded-sm bg-slate-800 border-slate-600' 
              />
              <svg className='top-2.5 left-2.5 absolute' xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="currentColor" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h1V6a5 5 0 0 1 5-5a5 5 0 0 1 5 5v2zm-6-5a3 3 0 0 0-3 3v2h6V6a3 3 0 0 0-3-3" />
              </svg>
              <span
                onClick={() => setShowPassword(!showPassword)}
                className='absolute top-2.5 right-2.5'
              >
                {showPassword ? 

                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <g fill="none">
                      <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                      <path fill="currentColor" d="M12 4c2.787 0 5.263 1.257 7.026 2.813c.885.781 1.614 1.658 2.128 2.531c.505.857.846 1.786.846 2.656s-.34 1.799-.846 2.656c-.514.873-1.243 1.75-2.128 2.531C17.263 18.743 14.786 20 12 20c-2.787 0-5.263-1.257-7.026-2.813c-.885-.781-1.614-1.658-2.128-2.531C2.34 13.799 2 12.87 2 12s.34-1.799.846-2.656c.514-.873 1.243-1.75 2.128-2.531C6.737 5.257 9.214 4 12 4m0 2c-2.184 0-4.208.993-5.702 2.312c-.744.656-1.332 1.373-1.729 2.047C4.163 11.049 4 11.62 4 12s.163.951.569 1.641c.397.674.985 1.39 1.729 2.047C7.792 17.007 9.816 18 12 18s4.208-.993 5.702-2.312c.744-.657 1.332-1.373 1.729-2.047c.406-.69.569-1.261.569-1.641s-.163-.951-.569-1.641c-.397-.674-.985-1.39-1.729-2.047C16.208 6.993 14.184 6 12 6m0 3q.132 0 .261.011a2 2 0 0 0 2.728 2.728A3 3 0 1 1 12 9" />
                    </g>
                  </svg>
                : 
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                      <path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" />
                      <path d="M16.681 16.673A8.7 8.7 0 0 1 12 18q-5.4 0-9-6q1.908-3.18 4.32-4.674m2.86-1.146A9 9 0 0 1 12 6q5.4 0 9 6q-1 1.665-2.138 2.87M3 3l18 18" />
                    </g>
                  </svg>
                  
              }
              </span>
            </div>

          </div>

          <p
            className='text-rose-400 w-full text-center'
          >
            {error}
          </p>

        </div>

        {/* buttons */}
        <div
          className='flex flex-col gap-5 '        
        >
          <button
            className='font-semibold p-2 w-full rounded bg-emerald-500 text-sm disabled:opacity-50'
            type='submit'
            disabled={isLoading}
          >
            {isLoading ? 'Processing...' : 'Submit'}
          </button>
          <Link 
            to='/' 
            className='w-full text-center opacity-50'
          >
            Already have an account? Sign in
          </Link>
        </div>

      </form>

    </div>
  )
}

export default RegisterPage