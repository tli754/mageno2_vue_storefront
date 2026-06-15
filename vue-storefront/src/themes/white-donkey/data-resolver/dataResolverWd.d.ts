import Task from "core/lib/sync/types/Task";

declare namespace DataResolverWd {
  interface AddressService {
    addressSuggestion: (query: string)=>Promise<Task>;
    addressDetails: (addressId: string)=>Promise<Task>;
  }
}
