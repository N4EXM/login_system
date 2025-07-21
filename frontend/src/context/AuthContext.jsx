import { createContext, useContext, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();

    const login = async (username, password) => {
        setIsLoading(true);
        try {
            const response = await fetch('http://localhost:3000/api/routes/api.php?action=login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (data.success && data.user != null) {
                setUser(data.user); // sets the values of the user e.g their username 
                setIsAuthenticated(true); // 
                navigate("/");
                return true;
            }
            return false;
        } catch (error) {
            console.error('Login failed:', error);
            return false;
        } finally {
            setIsLoading(false);
        }
    };

    const logout = async () => {
        try {
            await fetch('http://localhost:3000/backend/api.php?action=logout', {
                credentials: 'include'
            });
            
            setUser(null);
            setIsAuthenticated(false);
            navigate('/login');
        } catch (error) {
            console.error('Logout failed:', error);
        }
    };

    useEffect(() => {

        const checkAuth = async () => {

            try {

                const response = await fetch("http://localhost:3000/api/routes/api.php?action=check-auth", {

                    credentials: "include"

                })

                if (!response.ok) {
                    throw new Error("Auth check failed")
                }

                const data = await response.json()

                if (data.authenticated) {
                    setUser(data.user)
                    console.log(data)
                    setIsAuthenticated(true)
                    console.log("this ran")

                }

            }
            catch (error) {
                console.log("Auth check error: ", error)
            }
            finally {
                setIsLoading(false)
            }

        }

        checkAuth()

    }, [])

    return (
        <AuthContext.Provider value={{ 
            user, 
            isAuthenticated, 
            isLoading, 
            login, 
            logout 
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);