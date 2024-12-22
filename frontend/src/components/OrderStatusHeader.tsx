import { Order } from "@/types";
import { Progress } from "./ui/progress";
import { ORDER_STATUS } from "@/config/order-status-config";

type Props = {
  order: Order;
};

const OrderStatusHeader = ({ order }: Props) => {
  function getExpectedDelivery() {
    const created = new Date(order.created_at);
    // console.log("Created at:", created)
  
    // Add estimated hours and minutes separately
    const estimatedDeliveryTime = parseInt(String(order.restaurant.estimatedDeliveryTime)); // Assuming it's in minutes
    // console.log("estimated delivery time:", estimatedDeliveryTime)
    const additionalHours = Math.floor(estimatedDeliveryTime / 60); // Calculate additional hours
    // console.log("Additional hours:", additionalHours)
    const additionalMinutes = estimatedDeliveryTime % 60; // Calculate remaining minutes
    // console.log("Additional minutes:", additionalMinutes)
  
    created.setHours(created.getHours() + additionalHours); // Add additional hours
    created.setMinutes(created.getMinutes() + additionalMinutes); // Add additional minutes
  
    // Format hours into 12-hour format and determine AM/PM
    let hours = created.getHours();
    const minutes = created.getMinutes();
    const paddedMinutes = minutes < 10 ? `0${minutes}` : minutes;
  
    const amPm = hours >= 12 ? "PM" : "AM"; // Determine AM or PM
    hours = hours % 12 || 12; // Convert to 12-hour format (0 becomes 12)
  
    return `${hours}:${paddedMinutes} ${amPm}`;
  }
  

  function getOrderStatusInfo () {
    return (
      ORDER_STATUS.find((o) => o.value === order.status) || ORDER_STATUS[0]
    );
  };

  return (
    <>
      <h1 className="text-4xl font-bold tracking-tighter flex flex-col gap-5 md:flex-row md:justify-between">
        <span>Order Status: {getOrderStatusInfo().label}</span>
        <span>Expected by: {getExpectedDelivery()}</span>
      </h1>
      <Progress
        className="animate-pulse"
        value={getOrderStatusInfo().progressValue}
      />
    </>
  );
};

export default OrderStatusHeader;
