import { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const navigate = useNavigate();

    // Check authentication status on initial load
    // useEffect(() => {
    //     const checkAuth = async () => {
    //         try {
    //             const response = await fetch('http://localhost:8000/api.php?action=check-auth', {
    //                 credentials: 'include'
    //             });
    //             const data = await response.json();
                
    //             if (data.authenticated) {
    //                 setUser(data.user);
    //                 setIsAuthenticated(true);
    //             }
    //         } catch (error) {
    //             console.error('Auth check failed:', error);
    //         } finally {
    //             setIsLoading(false);
    //         }
    //     };
        
    //     checkAuth();
    // }, []);

    const login = async (username, password) => {
        try {
            const response = await fetch('http://localhost:8000/api.php?action=login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                setUser(data.user);
                setIsAuthenticated(true);
                return data.message;
            }
            return false;
        } catch (error) {
            console.error('Login failed:', error);
            return data.message;
        }
    };

    const logout = async () => {
        try {
            await fetch('http://localhost:8000/api.php?action=logout', {
                credentials: 'include'
            });
            
            setUser(null);
            setIsAuthenticated(false);
            navigate('/login');
        } catch (error) {
            console.error('Logout failed:', error);
        }
    };

    return (
        <AuthContext.Provider value={{ user, isAuthenticated, isLoading, login, logout }}>
            {!isLoading && children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);