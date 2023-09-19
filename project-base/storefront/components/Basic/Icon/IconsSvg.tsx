import { SVGProps } from 'react';

type SvgFC<P = object> = FC<P & SVGProps<SVGSVGElement>>;

export const ArrowIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M509.498 163.448l-53.446-53.445L256 310.054 55.95 110.003 2.503 163.448l226.775 226.775a37.8 37.8-90 0 0 53.445 0z"
            fill="currentColor"
        />
    </svg>
);

export const ArrowRightIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M501.396 236.195l-183.095-183.1C313.074 47.87 306.108 45 298.68 45c-7.437 0-14.399 2.873-19.625 8.095l-16.624 16.628c-5.222 5.219-8.1 12.189-8.1 19.62 0 7.428 2.878 14.633 8.1 19.852l106.816 107.05H29.89c-15.3 0-27.39 11.978-27.39 27.283v23.507c0 15.305 12.09 28.491 27.39 28.491h340.57L262.435 403.174c-5.222 5.227-8.1 12.007-8.1 19.439 0 7.423 2.878 14.303 8.1 19.525l16.624 16.575c5.227 5.226 12.189 8.075 19.624 8.075 7.428 0 14.394-2.886 19.62-8.112L501.4 275.58c5.24-5.243 8.12-12.242 8.1-19.682.016-7.465-2.86-14.468-8.104-19.703z"
            fill="currentColor"
        />
    </svg>
);

export const CartIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M117.806 2.5c-9.153 0-16.084 4.686-20.256 10.154C80.848 34.54 46.937 80.24 29.052 103.936c-.747.99-5.002 6.232-5.003 15.155L24 438.723c-.008 39.21 31.737 70.777 71.158 70.777h319.09c39.422 0 71.147-31.567 71.159-70.777l.097-319.583c.002-8.003-3.81-13.564-5.052-15.204-18.224-24.073-49.903-67.235-68.448-91.282C405.367 4.048 397.895 2.5 391.698 2.5zm12.68 50.718h248.532l30.469 40.533H99.896zm-55.938 90.643h360.068l.073 293.791c.003 13.95-10.04 21.378-20.257 21.378H94.926c-9.308 0-20.302-6.452-20.306-21.378zm63.392 53.3v25.357c0 64.123 52.517 116.69 116.64 116.69 64.123 0 116.638-52.567 116.638-116.69V197.16h-50.716v25.358c0 36.714-29.208 65.971-65.922 65.971-36.713 0-65.922-29.257-65.922-65.97V197.16z"
            fill="currentColor"
        />
    </svg>
);

export const CrossIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M509.5 256c0 140.004-113.496 253.5-253.5 253.5C115.996 509.5 2.5 396.004 2.5 256 2.5 115.996 115.996 2.5 256 2.5c140.004 0 253.5 113.496 253.5 253.5z"
            overflow="visible"
            fill="currentColor"
        />
        <path
            d="M163.248 123.419l-39.83 39.829L216.172 256l-92.752 92.752 39.829 39.83L256 295.828l92.752 92.752 39.83-39.829L295.828 256l92.752-92.752-39.829-39.83L256 216.172z"
            color="#000"
            overflow="visible"
            fill="#fff"
        />
    </svg>
);

export const SearchIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M225.772 2.5C102.663 2.5 2.5 102.663 2.5 225.772c0 123.116 100.163 223.272 223.272 223.272 123.116 0 223.272-100.156 223.272-223.272C449.044 102.663 348.888 2.5 225.772 2.5zm0 405.326c-100.383 0-182.053-81.67-182.053-182.053S125.39 43.719 225.772 43.719s182.053 81.67 182.053 182.053-81.67 182.054-182.053 182.054z"
            fill="currentColor"
        />
        <path
            d="M503.461 474.319L385.3 356.156c-8.052-8.051-21.091-8.051-29.143 0-8.051 8.045-8.051 21.098 0 29.143L474.32 503.46a20.538 20.538 0 0 0 14.571 6.039 20.551 20.551 0 0 0 14.571-6.039c8.052-8.044 8.052-21.098 0-29.142z"
            fill="currentColor"
        />
    </svg>
);

export const ChatIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M244.208.496c37.877-.09 75.223 8.779 109.04 25.84 82.575 41.269 134.844 125.767 134.88 218.08.078 32.778-9.529 64.314-22.366 94.358l46.52 139.615a26.947 26.947 0 01-34.1 34.102L338.564 465.97c-30.059 12.844-61.614 22.451-94.41 22.366-92.253-.057-176.694-52.277-217.975-134.775v-.053C9.092 319.66.19 282.278.288 244.362v-12.734a26.947 26.947 0 01.054-1.474C7.163 106.498 106.29 7.372 229.946.55a26.947 26.947 0 011.474-.053h12.735zm.052 53.889a26.947 26.947 0 01-.052 0h-11.473C136.24 59.82 59.613 136.448 54.177 232.944v11.472a26.947 26.947 0 010 .052 186.993 186.993 0 0020.103 84.78 26.947 26.947 0 01.053.105c32.196 64.422 97.855 105.066 169.875 105.094a26.947 26.947 0 01.052 0 186.978 186.978 0 0084.78-20.103 26.947 26.947 0 0120.682-1.473l94.358 31.417-31.418-94.358a26.947 26.947 0 011.474-20.682 186.977 186.977 0 0020.103-84.78 26.947 26.947 0 010-.052c-.028-72.02-40.672-137.679-105.094-169.876a26.947 26.947 0 01-.105-.052 186.982 186.982 0 00-84.78-20.103z"
            overflow="visible"
            fill="currentColor"
        />
    </svg>
);

export const MarkerIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
        <path
            overflow="visible"
            fill="currentColor"
            d="M408 120c0 54.6-73.1 151.9-105.2 192c-7.7 9.6-22 9.6-29.6 0C241.1 271.9 168 174.6 168 120C168 53.7 221.7 0 288 0s120 53.7 120 120zm8 80.4c3.5-6.9 6.7-13.8 9.6-20.6c.5-1.2 1-2.5 1.5-3.7l116-46.4C558.9 123.4 576 135 576 152V422.8c0 9.8-6 18.6-15.1 22.3L416 503V200.4zM137.6 138.3c2.4 14.1 7.2 28.3 12.8 41.5c2.9 6.8 6.1 13.7 9.6 20.6V451.8L32.9 502.7C17.1 509 0 497.4 0 480.4V209.6c0-9.8 6-18.6 15.1-22.3l122.6-49zM327.8 332c13.9-17.4 35.7-45.7 56.2-77V504.3L192 449.4V255c20.5 31.3 42.3 59.6 56.2 77c20.5 25.6 59.1 25.6 79.6 0zM288 152a40 40 0 1 0 0-80 40 40 0 1 0 0 80z"
        />
    </svg>
);

export const UserIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M255.992 315.584c-59.93 0-111.747 14.714-152.185 45.576C63.368 392.022 35.587 438.08 20 495.766l50.822 13.732c13.368-49.474 35.166-83.777 64.924-106.488 29.758-22.711 68.752-34.781 120.246-34.781 51.492 0 90.487 12.074 120.246 34.789 29.76 22.714 51.558 57.021 64.926 106.482l50.822-13.736C476.4 438.092 448.62 392.035 408.182 361.17c-40.437-30.865-92.257-45.586-152.19-45.586zm0-313.084c-72.376 0-131.613 59.237-131.613 131.613 0 72.377 59.237 131.616 131.613 131.616 72.377 0 131.615-59.24 131.615-131.616S328.37 2.5 255.992 2.5zm0 52.646c43.925 0 78.969 35.043 78.969 78.967 0 43.925-35.044 78.969-78.969 78.969s-78.967-35.044-78.967-78.969c0-43.924 35.042-78.967 78.967-78.967z"
            overflow="visible"
            fill="currentColor"
        />
    </svg>
);

export const CloseIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M302.449 255.999L499.864 58.577c12.848-12.842 12.848-33.604 0-46.446-12.841-12.841-33.604-12.841-46.445 0L255.997 209.553 58.581 12.13C45.734-.71 24.977-.71 12.136 12.131c-12.848 12.842-12.848 33.604 0 46.446L209.55 255.998 12.136 453.42c-12.848 12.842-12.848 33.604 0 46.446 6.4 6.406 14.814 9.623 23.222 9.623a32.756 32.756 0 0023.223-9.623l197.416-197.422 197.422 197.422a32.756 32.756 0 0023.223 9.623 32.756 32.756 0 0023.222-9.623c12.848-12.842 12.848-33.604 0-46.446zm0 0"
            fill="currentColor"
        />
    </svg>
);

export const MenuIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 12">
        <g fill="none">
            <path stroke="#000" d="M0 6H15.996 0" />
            <path fill="currentColor" d="M16 0v2H0V0zM15.996 10v2H0v-2z" />
        </g>
    </svg>
);

export const RemoveIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <g>
            <path
                d="M456.051 2.504L2.503 456.053l53.445 53.445L509.497 55.95z"
                overflow="visible"
                fill="currentColor"
            />
            <path d="M55.948 2.504L2.503 55.949 456.05 509.5l53.446-53.446z" overflow="visible" fill="currentColor" />
        </g>
    </svg>
);

export const NotImplementedYetIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M256 512c-68.38 0-132.667-26.629-181.02-74.98C26.629 388.667 0 324.38 0 256S26.629 123.333 74.98 74.98C123.333 26.629 187.62 0 256 0s132.667 26.629 181.02 74.98C485.371 123.333 512 187.62 512 256s-26.629 132.667-74.98 181.02C388.667 485.371 324.38 512 256 512zm0-472C136.897 40 40 136.897 40 256s96.897 216 216 216 216-96.897 216-216S375.103 40 256 40zm93.737 260.188c-9.319-5.931-21.681-3.184-27.61 6.136-.247.387-25.137 38.737-67.127 38.737s-66.88-38.35-67.127-38.737c-5.93-9.319-18.291-12.066-27.61-6.136s-12.066 18.292-6.136 27.61c1.488 2.338 37.172 57.263 100.873 57.263s99.385-54.924 100.873-57.263c5.93-9.319 3.183-21.68-6.136-27.61zM168 165c13.807 0 25 11.193 25 25s-11.193 25-25 25-25-11.193-25-25 11.193-25 25-25zm150 25c0 13.807 11.193 25 25 25s25-11.193 25-25-11.193-25-25-25-25 11.193-25 25z"
            fill="currentColor"
        />
    </svg>
);

export const TriangleIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="m84.125 188.612 252.164-171.93C373.509-8.695 424.253.905 449.63 38.125a81.566 81.566 0 0 1 14.174 45.949v343.86c0 45.048-36.518 81.566-81.566 81.566a81.568 81.568 0 0 1-45.95-14.174L84.126 323.396c-37.22-25.377-46.82-76.122-21.443-113.341a81.57 81.57 0 0 1 21.443-21.443z"
            fill="currentColor"
        />
    </svg>
);

export const SortIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21 14">
        <path d="M0 0h21v2H0zm0 6h12.833v2H0zm0 6h17.5v2H0z" fill="currentColor" />
    </svg>
);

export const RemoveBoldIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M489.846 394.952L350.899 256.005l138.947-138.947c26.204-26.204 26.204-68.69 0-94.894-26.204-26.204-68.69-26.204-94.894 0L255.994 161.11 117.047 22.153c-26.215-26.204-68.702-26.204-94.894 0-26.204 26.214-26.204 68.701 0 94.894L161.11 255.994 22.153 394.94c-26.204 26.214-26.204 68.701 0 94.894 13.118 13.107 30.274 19.65 47.452 19.65 17.167 0 34.346-6.554 47.453-19.65l138.936-138.936 138.958 138.947c13.118 13.096 30.274 19.65 47.452 19.65 17.179 0 34.346-6.554 47.453-19.65 26.193-26.204 26.193-68.68-.011-94.894z"
            fill="currentColor"
        />
    </svg>
);

export const RemoveThinIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M509.5 32.372L479.628 2.5 256 226.128 32.372 2.5 2.5 32.372 226.128 256 2.5 479.628 32.372 509.5 256 285.872 479.628 509.5l29.872-29.872L285.872 256z"
            fill="currentColor"
        />
    </svg>
);

export const PlusIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M2.5 287.632h221.868V509.5h63.264V287.632H509.5v-63.264H287.632V2.5h-63.264v221.868H2.5z"
            fill="currentColor"
        />
    </svg>
);

export const FilterIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <g transform="matrix(23.04546 0 0 23.04545 -9169.492 -192542.19)">
            <g stroke="none">
                <path d="M401.5 8357.5h1v7h-1zm0 10h1v7h-1zm7-2h1v9h-1zm0-8h1v5h-1z" fill="currentColor" />
                <path d="M412.5 8361.5v1h-7v-1zm7 8v1h-7v-1zm-14-2v1h-7v-1zm10-10h1v9h-1z" fill="currentColor" />
                <path d="M415.5 8369.5h1v5h-1z" fill="currentColor" />
            </g>
        </g>
    </svg>
);

export const PhoneIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M73.698 2.5c-41.49-.077-74.72 36.314-70.901 77.635a25.353 25.353 0 0 0 0 .346c7.961 74.689 33.436 146.425 74.317 209.435.02.03.03.069.05.1.012.018.037.03.05.049a472.057 472.057 0 0 0 145.118 144.871c.013.009.037-.008.05 0a480.098 480.098 0 0 0 209.039 74.219 25.353 25.353 0 0 0 .445.05c41.33 3.686 77.661-29.557 77.635-71.05v-68.673a25.353 25.353 0 0 0-6.14-16.438c-6.844-27.567-25.942-51.047-55.156-55.156a25.353 25.353 0 0 0-.198 0c-20.099-2.644-39.869-7.59-58.87-14.656h-.098c-25.934-9.696-55.337-3.446-75.06 15.992a25.353 25.353 0 0 0-.1.1l-13.665 13.615c-40.9-26.304-75.362-60.7-101.746-101.549l13.566-13.566a25.353 25.353 0 0 0 .148-.099c19.508-19.704 25.784-49.157 15.993-75.11a269.681 269.681 0 0 1-14.656-58.72 25.353 25.353 0 0 0-.05-.248c-5.022-35.17-35.672-61.504-71.197-61.147H73.697zm-.1 50.7a25.353 25.353 0 0 0 .1 0h68.821a25.353 25.353 0 0 0 .248 0c10.492-.105 19.064 7.24 20.547 17.626a320.18 320.18 0 0 0 17.379 69.614 25.353 25.353 0 0 0 .05.1c2.826 7.492 1.055 15.741-4.605 21.487l-29.064 29.014a25.353 25.353 0 0 0-4.11 30.5 392.043 392.043 0 0 0 147.15 146.901 25.353 25.353 0 0 0 30.45-4.11l29.063-29.013c5.763-5.651 14.127-7.444 21.686-4.605a25.353 25.353 0 0 0 .05 0 320.538 320.538 0 0 0 69.811 17.379c10.54 1.482 17.902 10.215 17.627 20.745a25.353 25.353 0 0 0 0 .644v68.722c.008 12.449-9.947 21.488-22.33 20.449-66.505-7.27-130.39-29.9-186.56-66.247a25.353 25.353 0 0 0-.199-.149c-52.27-33.146-96.627-77.363-129.87-129.572a25.353 25.353 0 0 0-.098-.198C83.194 206.199 60.452 142.06 53.299 75.332 52.257 63.048 61.24 53.168 73.6 53.2z"
            fill="currentColor"
        />
    </svg>
);

export const YoutubeIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M411.826 77.913H111.304C44.522 77.913 0 122.435 0 178.087v155.826c0 55.652 44.522 100.174 111.304 100.174h300.522c55.652 0 100.174-44.522 100.174-100.174V178.087c0-55.652-44.522-100.174-100.174-100.174ZM356.174 256l-144.696 66.783V178.087z"
            fill="currentColor"
        />
    </svg>
);

export const InstagramIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M365.678 2.307A1731.544 1731.544 0 0 0 95.62 18.19c-31.771 0-47.658 15.886-47.658 31.772-15.886 0-31.772 15.887-31.772 47.658L.307 145.277A1731.544 1731.544 0 0 0 16.19 415.336c0 31.771 15.886 47.656 31.772 47.656 0 15.886 15.887 31.772 47.658 31.772l47.656 15.886a1858.63 1858.63 0 0 0 270.059-15.886c47.657-15.886 63.542-31.77 79.428-79.428l15.886-47.658a1858.63 1858.63 0 0 0 0-222.4L492.764 97.62c0-31.771-15.886-47.658-31.772-47.658 0-15.886-15.885-31.772-47.656-31.772L365.678 2.307zM159.164 49.963A1795.087 1795.087 0 0 1 397.45 65.85l31.772 15.884 15.886 31.772 15.885 47.658a1795.087 1795.087 0 0 1 0 190.629l-15.885 47.656c0 31.772-15.886 47.658-47.658 47.658l-47.656 15.885a1795.087 1795.087 0 0 1-238.287-15.885l-31.772-15.886-15.884-31.772-15.887-47.656A1795.087 1795.087 0 0 1 63.85 113.506l15.884-31.772 31.772-15.884 47.658-15.887z"
            fill="currentColor"
        />
        <path
            d="M413.336 113.507a31.771 31.771 0 1 1-47.658 0 31.771 31.771 0 0 1 47.658 0zM254.479 129.393a127.086 127.086 0 1 0 0 254.171 127.086 127.086 0 0 0 0-254.171zm0 47.656a79.429 79.429 0 0 1 0 158.857 79.429 79.429 0 1 1 0-158.857z"
            fill="currentColor"
        />
    </svg>
);

export const MapMarkerIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 30 38" xmlns="http://www.w3.org/2000/svg">
        <path d="M30,15A15,15,0,1,0,10.089,29.161L15,38l4.911-8.839A14.994,14.994,0,0,0,30,15Z" fill="currentColor" />
    </svg>
);

export const CheckmarkIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M13.1667 1.55L4.82499 9.89167L1.03333 6.1"
            stroke="currentColor"
            strokeWidth="1.6"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

export const WarningIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M11.158 3.475c.37-.633 1.305-.633 1.684 0l9.029 15.109c.37.632-.098 1.416-.848 1.416H2.977c-.75 0-1.218-.784-.848-1.416ZM13 15h-2v2h2v-2Zm-1-6c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1Z"
            fill="currentColor"
        />
    </svg>
);

export const SpinnerIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 32 32" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path opacity=".25" d="M16 0 A16 16 0 0 0 16 32 A16 16 0 0 0 16 0 M16 4 A12 12 0 0 1 16 28 A12 12 0 0 1 16 4" />
        <path d="M16 0 A16 16 0 0 1 32 16 L28 16 A12 12 0 0 0 16 4z">
            <animateTransform
                attributeName="transform"
                type="rotate"
                from="0 16 16"
                to="360 16 16"
                dur="0.8s"
                repeatCount="indefinite"
            />
        </path>
    </svg>
);

export const InfoIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path
            d="M494.29 256c0 131.604-106.686 238.29-238.29 238.29-131.604 0-238.29-106.686-238.29-238.29C17.71 124.397 124.396 17.71 256 17.71c131.604 0 238.29 106.686 238.29 238.29z"
            fill="transparent"
            stroke="currentColor"
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="30.42"
        />
        <path
            fillRule="evenodd"
            clipRule="evenodd"
            d="M293.913 349.123H275.5V220.239c0-1.43-.161-2.822-.472-4.158-1.883-8.166-9.202-14.254-17.938-14.254h-36.825c-10.169 0-18.412 8.243-18.412 18.412 0 10.168 8.243 18.412 18.412 18.412h18.412v110.472h-18.412c-10.169 0-18.412 8.243-18.412 18.413 0 10.166 8.243 18.41 18.412 18.41h73.648c10.17 0 18.41-8.244 18.41-18.41 0-10.17-8.24-18.413-18.41-18.413z"
            fill="currentColor"
        />
        <path
            d="M256 134.318h.369"
            stroke="currentColor"
            strokeWidth="45.63"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

export const CompareIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 21">
        <path
            d="m10.117 14.488.083-.053-.083.053Zm.083.132 3.552 5.63h-7.16l3.608-5.63Z"
            stroke="currentColor"
            strokeWidth="1.5"
            strokeLinejoin="round"
            fill="transparent"
        />
        <path
            d="M4.7 9.624c0 1.134-.897 2.028-1.975 2.028-1.077 0-1.975-.894-1.975-2.028 0-1.133.898-2.027 1.975-2.027 1.078 0 1.976.894 1.976 2.027Z"
            stroke="currentColor"
            strokeWidth="1.5"
            fill="transparent"
        />
        <path d="M10.233 1v14.056" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
        <path
            d="M2.792 6.878V3.646h14.855v2.883"
            stroke="currentColor"
            strokeWidth="1.5"
            strokeLinecap="round"
            strokeLinejoin="round"
            fill="transparent"
        />
        <path
            d="M19.25 8.671c0 .95-.751 1.695-1.648 1.695-.897 0-1.648-.745-1.648-1.694 0-.95.751-1.695 1.648-1.695.897 0 1.648.745 1.648 1.694Z"
            stroke="currentColor"
            strokeWidth="1.5"
            fill="transparent"
        />
    </svg>
);

export const ArrowSecondaryIcon: SvgFC = (props) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 17">
        <path
            fillRule="evenodd"
            clipRule="evenodd"
            d="M8.994 16.8H7.006V4.668L1.42 10.213 0 8.79 8 .783l8 8.008-1.42 1.422-5.586-5.544v12.13Z"
            fill="currentColor"
        />
    </svg>
);

export const PlayIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 9 11" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="m1.4218 1.5 6.5 4.3333-6.5 4.3334V1.5Z"
            stroke="currentColor"
            strokeWidth="1.1304"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

export const HeartIcon: SvgFC<{ isFull: boolean }> = ({ isFull, ...props }) => (
    <svg {...props} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 19">
        <path
            clipRule="evenodd"
            d="M20.046 2.591a5.43 5.43 0 0 0-7.681 0l-1.047 1.047-1.046-1.047a5.431 5.431 0 0 0-7.681 7.681l1.046 1.047 6.267 6.267a2 2 0 0 0 2.829 0L19 11.319l1.046-1.047a5.43 5.43 0 0 0 0-7.68Z"
            fill={isFull ? 'currentColor' : 'transparent'}
            stroke="currentColor"
            strokeWidth="1.5"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

export const EmptyCartIcon: SvgFC = (props) => (
    <svg {...props} viewBox="0 0 572 512" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            fillRule="evenodd"
            clipRule="evenodd"
            d="M24 0C10.7 0 0 10.7 0 24C0 37.3 10.7 48 24 48H69.5C73.4 48 76.7 50.7 77.4 54.5L129 325.5C135.4 359.4 165.1 384 199.7 384H488C501.3 384 512 373.3 512 360C512 346.7 501.3 336 488 336H199.7C188.2 336 178.3 327.8 176.1 316.5L170.7 288H459.2C491.7 288 520.2 266.1 528.7 234.7L569.7 82.4C576.6 57 557.4 32 531.1 32H120.1C111 12.8 91.5 0 69.5 0H24ZM131.654 445.631C129.242 451.455 128 457.697 128 464C128 470.303 129.242 476.545 131.654 482.369C134.066 488.192 137.602 493.484 142.059 497.941C146.516 502.398 151.808 505.934 157.631 508.346C163.455 510.758 169.697 512 176 512C182.303 512 188.545 510.758 194.369 508.346C200.192 505.934 205.484 502.398 209.941 497.941C214.398 493.484 217.934 488.192 220.346 482.369C222.758 476.545 224 470.303 224 464C224 457.697 222.758 451.455 220.346 445.631C217.934 439.808 214.398 434.516 209.941 430.059C205.484 425.602 200.192 422.066 194.369 419.654C188.545 417.242 182.303 416 176 416C169.697 416 163.455 417.242 157.631 419.654C151.808 422.066 146.516 425.602 142.059 430.059C137.602 434.516 134.066 439.808 131.654 445.631ZM497.941 430.059C488.939 421.057 476.73 416 464 416C451.27 416 439.061 421.057 430.059 430.059C421.057 439.061 416 451.27 416 464C416 476.73 421.057 488.939 430.059 497.941C439.061 506.943 451.27 512 464 512C476.73 512 488.939 506.943 497.941 497.941C506.943 488.939 512 476.73 512 464C512 451.27 506.943 439.061 497.941 430.059ZM397.839 80.416C403.926 86.5195 403.926 96.4316 397.839 102.535L346.56 154L397.888 205.416C403.975 211.52 403.975 221.432 397.888 227.535C391.801 233.639 381.915 233.639 375.828 227.535L324.5 176.119L273.221 227.584C267.134 233.688 257.248 233.688 251.161 227.584C245.074 221.48 245.074 211.568 251.161 205.465L302.44 154L251.112 102.584C245.025 96.4805 245.025 86.5684 251.112 80.4648C257.199 74.3613 267.085 74.3613 273.172 80.4648L324.5 131.881L375.779 80.416C381.866 74.3125 391.752 74.3125 397.839 80.416Z"
            fill="currentColor"
        />
    </svg>
);
