import { Route, Routes } from 'react-router-dom';
import { AuthProvider } from './lib/auth';
import { ToastProvider } from './lib/toast';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Health } from './routes/Health';
import { Login } from './routes/Login';
import { Dashboard } from './routes/Dashboard';
import { InventoryItems } from './routes/InventoryItems';
import { InventoryItemForm } from './routes/InventoryItemForm';
import { WorkOrders } from './routes/WorkOrders';
import { WorkOrderForm } from './routes/WorkOrderForm';
import { Warehouses } from './routes/Warehouses';
import { WarehouseForm } from './routes/WarehouseForm';
import { Boms } from './routes/Boms';
import { BomForm } from './routes/BomForm';

export default function App() {
  return (
    <AuthProvider>
      <ToastProvider>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <Dashboard />
              </ProtectedRoute>
            }
          />
          <Route
            path="/inventory/items"
            element={
              <ProtectedRoute>
                <InventoryItems />
              </ProtectedRoute>
            }
          />
          <Route
            path="/inventory/items/new"
            element={
              <ProtectedRoute>
                <InventoryItemForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/inventory/items/:id/edit"
            element={
              <ProtectedRoute>
                <InventoryItemForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/inventory/warehouses"
            element={
              <ProtectedRoute>
                <Warehouses />
              </ProtectedRoute>
            }
          />
          <Route
            path="/inventory/warehouses/new"
            element={
              <ProtectedRoute>
                <WarehouseForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/inventory/warehouses/:id/edit"
            element={
              <ProtectedRoute>
                <WarehouseForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/manufacturing/boms"
            element={
              <ProtectedRoute>
                <Boms />
              </ProtectedRoute>
            }
          />
          <Route
            path="/manufacturing/boms/new"
            element={
              <ProtectedRoute>
                <BomForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/manufacturing/boms/:id/edit"
            element={
              <ProtectedRoute>
                <BomForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/manufacturing/work-orders"
            element={
              <ProtectedRoute>
                <WorkOrders />
              </ProtectedRoute>
            }
          />
          <Route
            path="/manufacturing/work-orders/new"
            element={
              <ProtectedRoute>
                <WorkOrderForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/manufacturing/work-orders/:id/edit"
            element={
              <ProtectedRoute>
                <WorkOrderForm />
              </ProtectedRoute>
            }
          />
          <Route
            path="/health"
            element={
              <ProtectedRoute>
                <Health />
              </ProtectedRoute>
            }
          />
        </Routes>
      </ToastProvider>
    </AuthProvider>
  );
}
