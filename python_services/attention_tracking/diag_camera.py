import cv2

def list_cameras():
    index = 0
    arr = []
    while index < 5:
        cap = cv2.VideoCapture(index, cv2.CAP_DSHOW)
        if cap.read()[0]:
            arr.append(index)
            print(f"Index {index} is available and working")
        else:
            print(f"Index {index} is NOT available or NOT working")
        cap.release()
        index += 1
    return arr

if __name__ == "__main__":
    list_cameras()
