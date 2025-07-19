import React from 'react'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import Header from './components/Header'
import Footer from './components/Footer'
import Home from './pages/Home'
import Products from './pages/Products'
import AboutUs from './pages/AboutUs'
import FAQ from './pages/FAQ'
import Contact from './pages/Contact'
import AdminLayout from './pages/admin/AdminLayout'
import Dashboard from './pages/admin/Dashboard'
import ProductsAdmin from './pages/admin/ProductsAdmin'
import OrdersAdmin from './pages/admin/OrdersAdmin'
import UsersAdmin from './pages/admin/UsersAdmin'
import { CartProvider } from './context/CartContext'

function App() {
  return (
    <CartProvider>
      <Router>
        <Routes>
          {/* Routes publiques */}
          <Route path="/" element={
            <div className="min-h-screen bg-white text-black">
              <Header />
              <main>
                <Home />
              </main>
              <Footer />
            </div>
          } />
          <Route path="/products" element={
            <div className="min-h-screen bg-white text-black">
              <Header />
              <main>
                <Products />
              </main>
              <Footer />
            </div>
          } />
          <Route path="/about" element={
            <div className="min-h-screen bg-white text-black">
              <Header />
              <main>
                <AboutUs />
              </main>
              <Footer />
            </div>
          } />
          <Route path="/faq" element={
            <div className="min-h-screen bg-white text-black">
              <Header />
              <main>
                <FAQ />
              </main>
              <Footer />
            </div>
          } />
          <Route path="/contact" element={
            <div className="min-h-screen bg-white text-black">
              <Header />
              <main>
                <Contact />
              </main>
              <Footer />
            </div>
          } />
          
          {/* Routes admin */}
          <Route path="/admin" element={<AdminLayout />}>
            <Route index element={<Dashboard />} />
            <Route path="products" element={<ProductsAdmin />} />
            <Route path="orders" element={<OrdersAdmin />} />
            <Route path="users" element={<UsersAdmin />} />
          </Route>
        </Routes>
      </Router>
    </CartProvider>
  )
}

export default App