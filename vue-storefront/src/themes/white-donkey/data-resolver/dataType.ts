export interface SuggestedAddress {
  address: string;
  id: string;
}

export interface AddressDetail {
  streetNumber: string;
  street: string;
  streetType: string;
  suburb: string;
  city: string;
  isRuralDelivery: boolean;
  postcode: number;
  depotName: string;
  addressId: number;
}
