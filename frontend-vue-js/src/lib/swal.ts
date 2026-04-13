import Swal from 'sweetalert2'

let activeRequests = 0

export function showLoader(title = 'Please wait...') {
  activeRequests += 1
  if (activeRequests === 1) {
    void Swal.fire({
      title,
      allowEscapeKey: false,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    })
  }
}

export function hideLoader() {
  activeRequests = Math.max(0, activeRequests - 1)
  if (activeRequests === 0) Swal.close()
}

export function showSuccess(message: string, title = 'Success') {
  return Swal.fire({
    icon: 'success',
    title,
    text: message,
    timer: 1500,
    showConfirmButton: false,
  })
}

export function showError(message: string, title = 'Error') {
  return Swal.fire({
    icon: 'error',
    title,
    text: message,
  })
}

export async function confirmAction(message: string, title = 'Are you sure?') {
  const result = await Swal.fire({
    icon: 'warning',
    title,
    text: message,
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'Cancel',
  })
  return result.isConfirmed
}

export function apiErrorMessage(error: any, fallback = 'Something went wrong') {
  return error?.response?.data?.message || error?.message || fallback
}
