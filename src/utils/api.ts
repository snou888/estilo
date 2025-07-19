const API_BASE_URL = 'http://localhost/estilo-ecommerce/backend/api';

export const api = {
  // Products
  getProducts: async () => {
    const response = await fetch(`${API_BASE_URL}/products/read.php`);
    return response.json();
  },

  getProduct: async (id: number) => {
    const response = await fetch(`${API_BASE_URL}/products/read_one.php?id=${id}`);
    return response.json();
  },

  // Users (placeholder for future implementation)
  login: async (email: string, password: string) => {
    // TODO: Implement login API call
    console.log('Login API call', { email, password });
  },

  register: async (userData: any) => {
    // TODO: Implement register API call
    console.log('Register API call', userData);
  }
};