import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from './context/AuthContext';

const RoleProtectedRoute = ({ allowedRoles, redirectPath = '/unauthorized', children }) => {
  const { user } = useAuth();

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (!allowedRoles.includes(user.role)) {
    return <Navigate to={redirectPath} replace />;
  }

  return children ? children : <Outlet />;
};

export default RoleProtectedRoute